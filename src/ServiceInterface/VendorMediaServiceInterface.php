<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorAttachmentDTO;
use App\DTO\VendorMediaUploadDTO;
use App\Entity\Vendor;
use App\Entity\VendorAttachment;
use App\Entity\VendorMedia;

/**
 * Application contract for vendor media service operations.
 */
interface VendorMediaServiceInterface
{
    /**
     * Creates or updates the requested aggregate state.
     */
    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia;

    /**
     * Executes the upload attachment operation for this runtime surface.
     */
    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment;
}
