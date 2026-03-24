<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Payout;

use App\DTO\Ledger\LedgerEntryDTO;
use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Vendor\Payout\Payout;
use App\Observability\Service\MetricEmitter;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Uid\Uuid;

final class VendorPayoutService implements VendorPayoutServiceInterface
{
    public function __construct(
        private readonly PayoutRepositoryInterface $repo,
        private readonly LedgerEntryRepositoryInterface $ledgerRepo,
        private readonly VendorLedgerServiceInterface $ledger,
        private readonly MetricEmitter $metrics,
    ) {
    }

    public function create(CreatePayoutDTO $dto): ?string
    {
        // 1) Получаем баланс в валюте
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
            return null; // недостаточно средств для выплаты
        }

        // 2) Рассчитываем комиссии/нетто
        $fee = (int) round($balanceCents * $dto->retentionFeePercent);
        $net = max(0, $balanceCents - $fee);

        // 3) Создаём payout
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
            meta: ['threshold' => $dto->thresholdCents, 'retention' => $dto->retentionFeePercent]
        );
        $this->repo->insert($payout);

        // 4) Записываем дебет в Ledger (резерв под выплату)
        $this->ledger->record(new LedgerEntryDTO(
            type: 'payout_reserve',
            entityId: $pid,
            sagaId: Uuid::v4()->toRfc4122(),
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

        // Тут должен быть вызов внешнего платёжного адаптера для перевода средств вендору (bank/stripe connect)
        // Для демо считаем успешным и записываем ledger: payout_processed (debit fee), payout_fee
        $this->ledger->record(new LedgerEntryDTO(
            type: 'payout_processed',
            entityId: $payoutId,
            sagaId: Uuid::v4()->toRfc4122(),
            vendorId: $p->vendorId,
            amountCents: $p->netCents,
            currency: $p->currency,
            direction: 'debit',
            meta: ['payoutId' => $payoutId]
        ));
        if ($p->feeCents > 0) {
            $this->ledger->record(new LedgerEntryDTO(
                type: 'payout_fee',
                entityId: $payoutId,
                sagaId: Uuid::v4()->toRfc4122(),
                vendorId: $p->vendorId,
                amountCents: $p->feeCents,
                currency: $p->currency,
                direction: 'debit',
                meta: ['payoutId' => $payoutId]
            ));
        }

        $this->repo->markProcessed($payoutId, (new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        $this->metrics->increment('payout_processed_total', ['currency' => $p->currency]);

        return true;
    }
}
