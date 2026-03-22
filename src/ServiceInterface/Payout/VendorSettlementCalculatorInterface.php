<?php

declare(strict_types=1);

namespace App\ServiceInterface\Payout;

interface VendorSettlementCalculatorInterface
{
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float;
}
