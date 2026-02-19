<?php
declare(strict_types = 1);

namespace App\DTO\Vendor\Payout;
final class PayoutAccountDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $provider,
        public string $accountRef,
        public string $currency,
        public bool   $active
    )
    {
    }
}
