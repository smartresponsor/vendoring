<?php

declare(strict_types=1);

namespace App\Service\Metric;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Metric\VendorMetricServiceInterface;

/**
 * Application service for vendor metric operations.
 */
final class VendorMetricService implements VendorMetricServiceInterface
{
    public function __construct(
        private readonly LedgerEntryRepositoryInterface $ledger,
    ) {
    }

    /**
     * Returns the overview projection for the requested runtime surface.
     */
    public function overview(string $tenantId, string $vendorId, ?string $from = null, ?string $to = null, string $currency = 'USD'): array
    {
        $revenue = max(0.0, $this->ledger->sumByAccount($tenantId, 'REVENUE', $from, $to, $vendorId, $currency));
        $refunds = max(0.0, $this->ledger->sumByAccount($tenantId, 'REFUNDS_PAYABLE', $from, $to, $vendorId, $currency));
        $payouts = max(0.0, $this->ledger->sumByAccount($tenantId, 'VENDOR_PAYABLE', $from, $to, $vendorId, $currency));
        $balance = $revenue - $refunds - $payouts;

        return [
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'from' => $from,
            'to' => $to,
            'currency' => $currency,
            'revenue' => $revenue,
            'refunds' => $refunds,
            'payouts' => $payouts,
            'balance' => $balance,
        ];
    }

    /**
     * Executes the trends operation for this runtime surface.
     */
    public function trends(string $tenantId, string $vendorId, string $from, string $to, string $bucket = 'month', string $currency = 'USD'): array
    {
        $o = $this->overview($tenantId, $vendorId, $from, $to, $currency);

        return [[
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'from' => $from,
            'to' => $to,
            'currency' => $currency,
            'bucket' => $bucket,
            'period' => $from.'..'.$to,
            'revenue' => $o['revenue'],
            'refunds' => $o['refunds'],
            'payouts' => $o['payouts'],
            'balance' => $o['balance'],
        ]];
    }
}
