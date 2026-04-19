<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorAttachment;
use App\Vendoring\Entity\VendorMedia;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorMediaServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia;

    /** @throws ORMException|OptimisticLockException */
    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment;
}
