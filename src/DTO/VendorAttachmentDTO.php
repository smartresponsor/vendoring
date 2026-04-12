<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class VendorAttachmentDTO
{
    public function __construct(
        public int $vendorId,
        public string $title,
        public string $filePath,
        public ?string $category = null,
    ) {}
}
