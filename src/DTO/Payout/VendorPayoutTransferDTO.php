<?php

declare(strict_types=1);

namespace App\DTO\Payout;

final readonly class VendorPayoutTransferDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $provider,
        public string $accountRef,
        public float $amount,
        public string $currency,
    ) {}
}
