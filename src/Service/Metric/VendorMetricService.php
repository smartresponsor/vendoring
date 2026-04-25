<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Metric;

use App\Vendoring\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Metric\VendorMetricTrendRequestDTO;
use App\Vendoring\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;

final readonly class VendorMetricService implements VendorMetricServiceInterface
{
    public function __construct(
        private LedgerEntryRepositoryInterface $ledger,
    ) {}

    public function overview(VendorMetricOverviewRequestDTO $request): array
    {
        $revenue = max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $request->tenantId,
            accountCode: 'REVENUE',
            from: $request->from,
            to: $request->to,
            vendorId: $request->vendorId,
            currency: $request->currency,
        )));
        $refunds = max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $request->tenantId,
            accountCode: 'REFUNDS_PAYABLE',
            from: $request->from,
            to: $request->to,
            vendorId: $request->vendorId,
            currency: $request->currency,
        )));
        $payouts = max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $request->tenantId,
            accountCode: 'VENDOR_PAYABLE',
            from: $request->from,
            to: $request->to,
            vendorId: $request->vendorId,
            currency: $request->currency,
        )));
        $balance = $revenue - $refunds - $payouts;

        return [
            'tenantId' => $request->tenantId,
            'vendorId' => $request->vendorId,
            'from' => $request->from,
            'to' => $request->to,
            'currency' => $request->currency,
            'revenue' => $revenue,
            'refunds' => $refunds,
            'payouts' => $payouts,
            'balance' => $balance,
        ];
    }

    public function trends(VendorMetricTrendRequestDTO $request): array
    {
        $overview = $this->overview(new VendorMetricOverviewRequestDTO(
            tenantId: $request->tenantId,
            vendorId: $request->vendorId,
            from: $request->from,
            to: $request->to,
            currency: $request->currency,
        ));

        return [[
            'tenantId' => $request->tenantId,
            'vendorId' => $request->vendorId,
            'from' => $request->from,
            'to' => $request->to,
            'currency' => $request->currency,
            'bucket' => $request->bucket,
            'period' => $request->from . '..' . $request->to,
            'revenue' => $overview['revenue'],
            'refunds' => $overview['refunds'],
            'payouts' => $overview['payouts'],
            'balance' => $overview['balance'],
        ]];
    }
}
