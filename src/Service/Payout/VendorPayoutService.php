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
use Doctrine\DBAL\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final readonly class VendorPayoutService implements VendorPayoutServiceInterface
{
    public function __construct(
        private PayoutRepositoryInterface      $repo,
        private LedgerEntryRepositoryInterface $ledgerRepo,
        private VendorLedgerServiceInterface   $ledger,
        private MetricCollectorInterface       $metrics,
        private RuntimeLoggerInterface         $runtimeLogger,
    ) {}

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws RandomException
     */
    public function create(CreatePayoutDTO $dto): ?string
    {
        // 1) Получаем баланс в валюте
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

            return null; // недостаточно средств для выплаты
        }

        // 2) Рассчитываем комиссии/нетто
        $fee = (int) round($balanceCents * $dto->retentionFeePercent);
        $net = max(0, $balanceCents - $fee);

        // 3) Создаём payout
        $payoutId = Uuid::v4()->toRfc4122();
        $payout = new Payout(
            id: $payoutId,
            vendorId: $dto->vendorId,
            currency: $dto->currency,
            grossCents: $balanceCents,
            feeCents: $fee,
            netCents: $net,
            status: 'pending',
            createdAt: (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            meta: [
                'tenantId' => $dto->tenantId,
                'threshold' => $dto->thresholdCents,
                'retention' => $dto->retentionFeePercent,
            ],
        );
        $this->repo->insert($payout);

        // 4) Записываем дебет в Ledger (резерв под выплату)
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

    /**
     * @param string $payoutId
     * @return bool
     * @throws Exception
     * @throws RandomException
     */
    public function process(string $payoutId): bool
    {
        $payout = $this->repo->byId($payoutId);
        if (null === $payout || 'pending' !== $payout->status) {
            $this->runtimeLogger->warning('vendor_payout_process_rejected', [
                'payout_id' => $payoutId,
                'error_code' => 'payout_not_pending',
            ]);

            return false;
        }

        // Тут должен быть вызов внешнего платёжного адаптера для перевода средств вендору (bank/stripe connect)
        // Для демо считаем успешным и записываем ledger: payout_processed (debit fee), payout_fee
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

        $this->repo->markProcessed($payoutId, (new DateTimeImmutable())->format('Y-m-d H:i:s'));
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
