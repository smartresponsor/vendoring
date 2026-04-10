<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorAttachmentDTO;
use App\DTO\VendorMediaUploadDTO;
use App\Entity\Vendor;
use App\Entity\VendorAttachment;
use App\Entity\VendorMedia;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorMediaServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia;

    /** @throws ORMException|OptimisticLockException */
    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment;
}
