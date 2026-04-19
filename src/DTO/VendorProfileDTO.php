<?php

declare(strict_types=1);

namespace App\Vendoring\DTO;

final readonly class VendorProfileDTO
{
    /** @param array<string, string>|null $socials */
    public function __construct(
        public int $vendorId,
        public ?string $displayName = null,
        public ?string $about = null,
        public ?string $website = null,
        public ?array $socials = null,
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $publicationAction = null,
    ) {}
}
