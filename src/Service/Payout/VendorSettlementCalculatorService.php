<?php

declare(strict_types=1);

namespace App\Service\Payout;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Payout\VendorSettlementCalculatorServiceInterface;

/**
 * Application service for vendor settlement calculator operations.
 */
final class VendorSettlementCalculatorService implements VendorSettlementCalculatorServiceInterface
{
    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger)
    {
    }

    /** Simplified: net = debit(VENDOR_PAYABLE) - credit(VENDOR_PAYABLE) over period. */
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float
    {
        return max(0.0, $this->ledger->sumByAccount($tenantId, 'VENDOR_PAYABLE', $from, $to, $vendorId, $currency));
    }
}
