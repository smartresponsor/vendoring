<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\DTO\Vendor\VendorAttachmentDTO;
use App\DTO\Vendor\VendorMediaUploadDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorAttachment;
use App\Entity\Vendor\VendorMedia;

interface VendorMediaServiceInterface
{
    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia;

    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment;
}
