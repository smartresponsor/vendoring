<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorAttachmentDTO;
use App\DTO\VendorMediaUploadDTO;
use App\Entity\Vendor;
use App\Entity\VendorAttachment;
use App\Entity\VendorMedia;

interface VendorMediaServiceInterface
{
    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia;

    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment;
}
