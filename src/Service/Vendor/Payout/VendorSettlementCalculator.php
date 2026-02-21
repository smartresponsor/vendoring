<?php

declare(strict_types=1);

namespace App\Service\Vendor\Payout;

use App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Vendor\Payout\VendorSettlementCalculatorInterface;

final class VendorSettlementCalculator implements VendorSettlementCalculatorInterface
{
    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger)
    {
    }

    /** Simplified: net = debit(VENDOR_PAYABLE) - credit(VENDOR_PAYABLE) over period. */
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float
    {
        // For demo, reuse account summary without vendor dimension.
        return max(0.0, $this->ledger->sumByAccount($tenantId, 'VENDOR_PAYABLE'));
    }
}
