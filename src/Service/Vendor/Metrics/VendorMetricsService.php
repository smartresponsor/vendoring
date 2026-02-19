<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Metrics;

use App\ServiceInterface\Vendor\Metrics\VendorMetricsServiceInterface;
use App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface;
use App\RepositoryInterface\Vendor\Payout\PayoutRepositoryInterface;

final class VendorMetricsService implements VendorMetricsServiceInterface
{
    public function __construct(
        private readonly LedgerEntryRepositoryInterface $ledger,
        private readonly PayoutRepositoryInterface      $payouts
    )
    {
    }

    public function overview(string $tenantId, string $vendorId, ?string $from = null, ?string $to = null, string $currency = 'USD'): array
    {
        $revenue = max(0.0, $this->ledger->sumByAccount($tenantId, 'REVENUE', $from, $to, $vendorId));
        $refunds = max(0.0, $this->ledger->sumByAccount($tenantId, 'REFUNDS_PAYABLE', $from, $to, $vendorId));
        $payouts = max(0.0, $this->ledger->sumByAccount($tenantId, 'VENDOR_PAYABLE', $from, $to, $vendorId));
        $balance = $revenue - $refunds - $payouts;
        return ['revenue' => $revenue, 'refunds' => $refunds, 'payouts' => $payouts, 'balance' => $balance];
    }

    public function trends(string $tenantId, string $vendorId, string $from, string $to, string $bucket = 'month'): array
    {
        $o = $this->overview($tenantId, $vendorId, $from, $to);
        return [[
            'period' => $from . '..' . $to,
            'revenue' => $o['revenue'],
            'refunds' => $o['refunds'],
            'payouts' => $o['payouts'],
            'balance' => $o['balance'],
        ]];
    }
}
