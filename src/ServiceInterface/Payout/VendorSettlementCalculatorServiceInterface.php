<?php

declare(strict_types=1);

namespace App\ServiceInterface\Payout;

/**
 * Application contract for vendor settlement calculator service operations.
 */
interface VendorSettlementCalculatorServiceInterface
{
    /**
     * Executes the net for period operation for this runtime surface.
     */
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float;
}
