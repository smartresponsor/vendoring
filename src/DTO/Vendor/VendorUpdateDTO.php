<?php
declare(strict_types = 1);

namespace App\DTO\Vendor;

final readonly class VendorUpdateDTO
{
    public function __construct(
        public ?string $brandName = null,
        public ?string $status = null
    )
    {
    }
}
