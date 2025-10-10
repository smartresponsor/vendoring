<?php
declare(strict_types=1);

namespace App\DTO\Vendor;

final readonly class VendorCreateDTO
{
    public function __construct(
        public string $brandName,
        public ?string $iban = null,
        public ?string $swift = null,
        public ?string $taxId = null,
        public ?string $country = null
    ) {}
}
