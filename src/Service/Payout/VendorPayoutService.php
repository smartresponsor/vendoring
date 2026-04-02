<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Ledger\LedgerEntryDTO;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Payout\Payout;
use App\Observability\Service\MetricEmitter;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Uid\Uuid;

final class VendorPayoutService implements VendorPayoutServiceInterface
{
    public function __construct(
        private readonly PayoutRepositoryInterface $repo,
        private readonly LedgerEntryRepositoryInterface $ledgerRepo,
        private readonly VendorLedgerServiceInterface $ledger,
        private readonly PayoutAccountRepositoryInterface $accounts,
        private readonly VendorPayoutProviderServiceInterface $provider,
        private readonly MetricEmitter $metrics,
    ) {
    }

    public function create(CreatePayoutDTO $dto): ?string
    {
        $balances = $this->ledgerRepo->balancesForVendor($dto->vendorId);
        $cur = null;
        foreach ($balances as $b) {
            if ($b->currency === $dto->currency) {
                $cur = $b;
                break;
            }
        }
        $balanceCents = $cur ? $cur->balanceCents : 0;

        if ($balanceCents < $dto->thresholdCents) {
            return null;
        }

        $fee = (int) round($balanceCents * $dto->retentionFeePercent);
        $net = max(0, $balanceCents - $fee);

        $pid = Uuid::v4()->toRfc4122();
        $payout = new Payout(
            id: $pid,
            vendorId: $dto->vendorId,
            currency: $dto->currency,
            grossCents: $balanceCents,
            feeCents: $fee,
            netCents: $net,
            status: 'pending',
            createdAt: (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            meta: [
                'tenantId' => $dto->tenantId,
                'threshold' => $dto->thresholdCents,
                'retention' => $dto->retentionFeePercent,
            ]
        );
        $this->repo->insert($payout);

        $this->ledger->record(new LedgerEntryDTO(
            type: 'payout_reserve',
            entityId: $pid,
            sagaId: Uuid::v4()->toRfc4122(),
            tenantId: $dto->tenantId,
            vendorId: $dto->vendorId,
            amountCents: $net,
            currency: $dto->currency,
            direction: 'debit',
            meta: ['payoutId' => $pid]
        ));

        $this->metrics->increment('payout_created_total', ['currency' => $dto->currency]);

        return $pid;
    }

    public function process(string $payoutId): bool
    {
        $p = $this->repo->byId($payoutId);
        if (!$p || 'pending' !== $p->status) {
            return false;
        }

        $processedAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $tenantId = $this->tenantIdFromMeta($p);
        $account = $this->accounts->get($tenantId, $p->vendorId);

        if (null === $account || !$account->active) {
            $this->repo->markFailed($payoutId, $processedAt, [
                'tenantId' => $tenantId,
                'error' => 'payout_account_unavailable',
            ]);
            $this->metrics->increment('payout_failed_total', ['currency' => $p->currency]);

            return false;
        }

        if ($account->currency !== $p->currency) {
            $this->repo->markFailed($payoutId, $processedAt, [
                'tenantId' => $tenantId,
                'provider' => $account->provider,
                'accountRef' => $account->accountRef,
                'error' => 'payout_currency_mismatch',
                'accountCurrency' => $account->currency,
            ]);
            $this->metrics->increment('payout_failed_total', ['currency' => $p->currency, 'provider' => $account->provider]);

            return false;
        }

        $result = $this->provider->transfer(
            $tenantId,
            $p->vendorId,
            $account->provider,
            $account->accountRef,
            $p->netCents / 100,
            $p->currency,
        );

        if (true !== ($result['ok'] ?? false)) {
            $this->repo->markFailed($payoutId, $processedAt, [
                'tenantId' => $tenantId,
                'provider' => $account->provider,
                'accountRef' => $account->accountRef,
                'providerRef' => is_scalar($result['ref'] ?? null) ? (string) $result['ref'] : null,
                'error' => is_scalar($result['error'] ?? null) ? (string) $result['error'] : 'payout_provider_transfer_failed',
            ]);
            $this->metrics->increment('payout_failed_total', ['currency' => $p->currency, 'provider' => $account->provider]);

            return false;
        }

        $providerRef = is_scalar($result['ref'] ?? null) ? (string) $result['ref'] : null;

        $this->ledger->record(new LedgerEntryDTO(
            type: 'payout_processed',
            entityId: $payoutId,
            sagaId: Uuid::v4()->toRfc4122(),
            tenantId: $tenantId,
            vendorId: $p->vendorId,
            amountCents: $p->netCents,
            currency: $p->currency,
            direction: 'debit',
            meta: array_filter([
                'payoutId' => $payoutId,
                'provider' => $account->provider,
                'providerRef' => $providerRef,
            ], static fn (mixed $value): bool => null !== $value)
        ));
        if ($p->feeCents > 0) {
            $this->ledger->record(new LedgerEntryDTO(
                type: 'payout_fee',
                entityId: $payoutId,
                sagaId: Uuid::v4()->toRfc4122(),
                tenantId: $tenantId,
                vendorId: $p->vendorId,
                amountCents: $p->feeCents,
                currency: $p->currency,
                direction: 'debit',
                meta: array_filter([
                    'payoutId' => $payoutId,
                    'provider' => $account->provider,
                    'providerRef' => $providerRef,
                ], static fn (mixed $value): bool => null !== $value)
            ));
        }

        $this->repo->markProcessed($payoutId, $processedAt, array_filter([
            'tenantId' => $tenantId,
            'provider' => $account->provider,
            'accountRef' => $account->accountRef,
            'providerRef' => $providerRef,
        ], static fn (mixed $value): bool => null !== $value));
        $this->metrics->increment('payout_processed_total', ['currency' => $p->currency, 'provider' => $account->provider]);

        return true;
    }

    private function tenantIdFromMeta(Payout $payout): string
    {
        $tenantId = $payout->meta['tenantId'] ?? null;

        return is_string($tenantId) && '' !== trim($tenantId) ? $tenantId : 'default';
    }
}
