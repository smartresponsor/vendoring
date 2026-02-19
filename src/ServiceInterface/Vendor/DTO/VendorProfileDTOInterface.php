<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorProfileDTOInterface
{

    public function __construct(public int $vendorId, public ?string $displayName = null, public ?string $about = null, public ?string $website = null, public ?array $socials = null, public ?string $seoTitle = null, public ?string $seoDescription = null);
}
