<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Payout;

interface VendorSettlementCalculatorServiceInterface
{
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float;
}
