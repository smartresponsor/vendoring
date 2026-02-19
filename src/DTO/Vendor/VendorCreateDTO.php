<?php
declare(strict_types = 1);

namespace App\DTO\Vendor;

final readonly class VendorCreateDTO
{
    public function __construct(
        public string $brandName,
        public ?int   $userId = null
    )
    {
    }
}
