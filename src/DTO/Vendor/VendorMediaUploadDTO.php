<?php
declare(strict_types = 1);

namespace App\DTO\Vendor;

final readonly class VendorMediaUploadDTO
{
    public function __construct(
        public int     $vendorId,
        public ?string $logoPath = null,
        public ?string $bannerPath = null,
        public ?array  $gallery = null
    )
    {
    }
}
