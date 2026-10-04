<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Payout;

use App\Vendoring\DTO\Ledger\VendorLedgerDTO;
use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\RepositoryInterface\VendorLedgerRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorPayoutRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorMetricCollectorServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Doctrine\DBAL\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final readonly class VendorPayoutService implements VendorPayoutServiceInterface
{
    public function __construct(
        private VendorPayoutRepositoryInterface $repo,
        private VendorLedgerRepositoryInterface $ledgerRepo,
        private VendorLedgerServiceInterface $ledger,
        private VendorMetricCollectorServiceInterface $metrics,
        private VendorRuntimeLoggerServiceInterface $runtimeLogger,
    ) {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws RandomException
     */
    public function create(VendorCreatePayoutDTO $dto): ?string
    {
        // 1) РџРѕР»СѓС‡Р°РµРј Р±Р°Р»Р°РЅСЃ РІ РІР°Р»СЋС‚Рµ
        $balances = $this->ledgerRepo->balancesForVendor($dto->vendorId);
        $matchedBalance = null;
        foreach ($balances as $balance) {
            if ($balance->currency === $dto->currency) {
                $matchedBalance = $balance;
                break;
            }
        }
        $balanceCents = null === $matchedBalance ? 0 : $matchedBalance->balanceCents;

        if ($balanceCents < $dto->thresholdCents) {
            $this->runtimeLogger->info('vendor_payout_skipped_insufficient_balance', [
                'vendor_id' => $dto->vendorId,
                'currency' => $dto->currency,
                'balance_cents' => (string) $balanceCents,
                'threshold_cents' => (string) $dto->thresholdCents,
            ]);

            return null; // РЅРµРґРѕСЃС‚Р°С‚РѕС‡РЅРѕ СЃСЂРµРґСЃС‚РІ РґР»СЏ РІС‹РїР»Р°С‚С‹
        }

        // 2) Р Р°СЃСЃС‡РёС‚С‹РІР°РµРј РєРѕРјРёСЃСЃРёРё/РЅРµС‚С‚Рѕ
        $fee = (int) round($balanceCents * $dto->retentionFeePercent);
        $net = max(0, $balanceCents - $fee);

        // 3) РЎРѕР·РґР°С‘Рј payout
        $payoutId = Uuid::v4()->toRfc4122();

        $payout = new VendorPayoutEntity(
            id: $payoutId,
            vendorId: $dto->vendorId,
            currency: $dto->currency,
            grossCents: $balanceCents,
            feeCents: $fee,
            netCents: $net,
            status: 'pending',
            meta: [
                'threshold' => $dto->thresholdCents,
                'retention' => $dto->retentionFeePercent,
            ],
        );
        $this->repo->insert($payout);

        // 4) Р—Р°РїРёСЃС‹РІР°РµРј РґРµР±РµС‚ РІ Ledger (СЂРµР·РµСЂРІ РїРѕРґ РІС‹РїР»Р°С‚Сѓ)
        $this->ledger->record(new VendorLedgerDTO(
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

        // РўСѓС‚ РґРѕР»Р¶РµРЅ Р±С‹С‚СЊ РІС‹Р·РѕРІ РІРЅРµС€РЅРµРіРѕ РїР»Р°С‚С‘Р¶РЅРѕРіРѕ Р°РґР°РїС‚РµСЂР° РґР»СЏ РїРµСЂРµРІРѕРґР° СЃСЂРµРґСЃС‚РІ РІРµРЅРґРѕСЂСѓ (bank/stripe connect)
        // Р”Р»СЏ РґРµРјРѕ СЃС‡РёС‚Р°РµРј СѓСЃРїРµС€РЅС‹Рј Рё Р·Р°РїРёСЃС‹РІР°РµРј ledger: payout_processed (debit fee), payout_fee
        $this->ledger->record(new VendorLedgerDTO(
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
            $this->ledger->record(new VendorLedgerDTO(
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

        $processedAt = new \DateTimeImmutable();
        $this->repo->markProcessed($payoutId, $processedAt->format('Y-m-d H:i:s'));
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
