<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Ledger\LedgerEntryDTO;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Payout\Payout;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use App\ServiceInterface\Observability\MetricCollectorInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Throwable;

final readonly class VendorPayoutService implements VendorPayoutServiceInterface
{
    public function __construct(
        private PayoutRepositoryInterface $repo,
        private LedgerEntryRepositoryInterface $ledgerRepo,
        private VendorLedgerServiceInterface $ledger,
        private MetricCollectorInterface $metrics,
        private RuntimeLoggerInterface $runtimeLogger,
    ) {}

    public function create(CreatePayoutDTO $dto): ?string
    {
        $balances = $this->ledgerRepo->balancesForVendor($dto->vendorId);
        $matchedBalance = null;
        foreach ($balances as $balance) {
            if ($balance->currency === $dto->currency) {
                $matchedBalance = $balance;
                break;
            }
        }
        $balanceCents = $matchedBalance?->balanceCents ?? 0;

        if ($balanceCents < $dto->thresholdCents) {
            $this->runtimeLogger->info('vendor_payout_skipped_insufficient_balance', [
                'vendor_id' => $dto->vendorId,
                'currency' => $dto->currency,
                'balance_cents' => (string) $balanceCents,
                'threshold_cents' => (string) $dto->thresholdCents,
            ]);

            return null;
        }

        $fee = (int) round($balanceCents * $dto->retentionFeePercent);
        $net = max(0, $balanceCents - $fee);

        try {
            $payoutId = Uuid::v4()->toRfc4122();
            $payout = new Payout(
                id: $payoutId,
                vendorId: $dto->vendorId,
                currency: $dto->currency,
                grossCents: $balanceCents,
                feeCents: $fee,
                netCents: $net,
                status: 'pending',
                createdAt: new DateTimeImmutable()->format('Y-m-d H:i:s'),
                meta: ['threshold' => $dto->thresholdCents, 'retention' => $dto->retentionFeePercent],
            );
            $this->repo->insert($payout);

            $this->ledger->record(new LedgerEntryDTO(
                type: 'payout_reserve',
                entityId: $payoutId,
                sagaId: Uuid::v4()->toRfc4122(),
                vendorId: $dto->vendorId,
                amountCents: $net,
                currency: $dto->currency,
                direction: 'debit',
                meta: ['payoutId' => $payoutId],
            ));
        } catch (Throwable $exception) {
            $this->runtimeLogger->error('vendor_payout_create_failed', [
                'vendor_id' => $dto->vendorId,
                'currency' => $dto->currency,
                'error_class' => $exception::class,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->metrics->increment('payout_created_total', ['currency' => $dto->currency]);
        $this->runtimeLogger->info('vendor_payout_created', [
            'vendor_id' => $dto->vendorId,
            'payout_id' => $payoutId,
            'currency' => $dto->currency,
            'gross_cents' => (string) $balanceCents,
            'net_cents' => (string) $net,
        ]);

        return $payoutId;
    }

    public function process(string $payoutId): bool
    {
        $payout = $this->repo->byId($payoutId);
        if (!$payout || 'pending' !== $payout->status) {
            $this->runtimeLogger->warning('vendor_payout_process_rejected', [
                'payout_id' => $payoutId,
                'error_code' => 'payout_not_pending',
            ]);

            return false;
        }

        try {
            $this->ledger->record(new LedgerEntryDTO(
                type: 'payout_processed',
                entityId: $payoutId,
                sagaId: Uuid::v4()->toRfc4122(),
                vendorId: $payout->vendorId,
                amountCents: $payout->netCents,
                currency: $payout->currency,
                direction: 'debit',
                meta: ['payoutId' => $payoutId],
            ));
            if ($payout->feeCents > 0) {
                $this->ledger->record(new LedgerEntryDTO(
                    type: 'payout_fee',
                    entityId: $payoutId,
                    sagaId: Uuid::v4()->toRfc4122(),
                    vendorId: $payout->vendorId,
                    amountCents: $payout->feeCents,
                    currency: $payout->currency,
                    direction: 'debit',
                    meta: ['payoutId' => $payoutId],
                ));
            }

            $this->repo->markProcessed($payoutId, new DateTimeImmutable()->format('Y-m-d H:i:s'));
        } catch (Throwable $exception) {
            $this->runtimeLogger->error('vendor_payout_process_failed', [
                'vendor_id' => $payout->vendorId,
                'payout_id' => $payoutId,
                'currency' => $payout->currency,
                'error_class' => $exception::class,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->metrics->increment('payout_processed_total', ['currency' => $payout->currency]);
        $this->runtimeLogger->info('vendor_payout_processed', [
            'vendor_id' => $payout->vendorId,
            'payout_id' => $payoutId,
            'currency' => $payout->currency,
            'net_cents' => (string) $payout->netCents,
        ]);

        return true;
    }
}
