<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Payout;

final class VendorPayoutAccountDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $provider,
        public string $accountRef,
        public string $currency,
        public bool $active,
    ) {}
}
