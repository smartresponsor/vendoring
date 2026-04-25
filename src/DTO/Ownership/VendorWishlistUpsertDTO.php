<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

final readonly class VendorWishlistUpsertDTO
{
    public function __construct(
        public int $vendorId,
        public string $customerReference,
        public string $name,
        public string $status = 'active',
        public ?string $targetType = null,
        public ?string $targetId = null,
        public int $quantity = 1,
        public ?string $note = null,
    ) {}
}
