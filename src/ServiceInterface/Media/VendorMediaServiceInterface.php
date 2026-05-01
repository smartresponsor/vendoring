<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Media;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorMediaServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsertMedia(VendorEntity $vendor, VendorMediaUploadDTO $dto): VendorMediaEntity;

    /** @throws ORMException|OptimisticLockException */
    public function uploadAttachment(VendorEntity $vendor, VendorAttachmentDTO $dto): VendorAttachmentEntity;
}
