<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

final readonly class VendorCategoryUpsertDTO
{
    public function __construct(
        public int $vendorId,
        public string $categoryCode,
        public ?string $categoryName = null,
        public bool $isPrimary = false,
    ) {}
}
