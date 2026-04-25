<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

final readonly class VendorFavouriteUpsertDTO
{
    public function __construct(
        public int $vendorId,
        public string $targetType,
        public string $targetId,
        public ?string $note = null,
    ) {}
}
