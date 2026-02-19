<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorAttachmentDTOInterface
{

    public function __construct(public int $vendorId, public string $title, public string $filePath, public ?string $category = null);
}
