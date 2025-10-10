<?php
declare(strict_types=1);

namespace App\DTO\Vendor;

final readonly class VendorDocumentDTO
{
    public function __construct(
        public int $vendorId,
        public string $type,
        public string $filePath,
        public ?\DateTimeImmutable $expiresAt = null,
        public ?int $uploaderId = null
    ) {}
}
