<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class VendorBillingDTO
{
    public function __construct(
        public int $vendorId,
        public ?string $iban = null,
        public ?string $swift = null,
        public string $payoutMethod = 'bank',
        public ?string $billingEmail = null,
    ) {}
}
