<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorDocumentDTOInterface
{

    public function __construct(public int $vendorId, public string $type, public string $filePath, public ?\DateTimeImmutable $expiresAt = null, public ?int $uploaderId = null);
}
