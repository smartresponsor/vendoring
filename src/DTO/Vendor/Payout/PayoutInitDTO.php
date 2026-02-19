<?php
declare(strict_types = 1);

namespace App\DTO\Vendor\Payout;
final class PayoutInitDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $periodStart,
        public string $periodEnd,
        public string $currency
    )
    {
    }
}
