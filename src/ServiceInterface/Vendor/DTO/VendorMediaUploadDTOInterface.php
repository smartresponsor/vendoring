<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorMediaUploadDTOInterface
{

    public function __construct(public int $vendorId, public ?string $logoPath = null, public ?string $bannerPath = null, public ?array $gallery = null);
}
