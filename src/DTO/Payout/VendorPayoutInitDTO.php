<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Payout;

final class VendorPayoutInitDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $periodStart,
        public string $periodEnd,
        public string $currency,
    ) {}
}
