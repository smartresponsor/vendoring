<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Payout;

use App\Vendoring\DTO\Ledger\VendorLedgerDTO;
use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
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
        $balances = $this->ledgerRepo->balancesForVendor($dto->tenantId, $dto->vendorId);
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
            tenantId: $dto->tenantId,
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
            tenantId: $dto->tenantId,
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
     * @throws VendorPayoutProviderNotConfiguredException
     * @throws RandomException
     */
    public function process(string $tenantId, string $payoutId): bool
    {
        $payout = $this->repo->byId($tenantId, $payoutId);
        if (null === $payout || 'pending' !== $payout->status) {
            $this->runtimeLogger->warning('vendor_payout_process_rejected', [
                'payout_id' => $payoutId,
                'error_code' => 'payout_not_pending',
            ]);

            return false;
        }

        // No ledger or payout state mutation without real provider confirmation.
        throw new VendorPayoutProviderNotConfiguredException('vendor_payout');
    }
}
