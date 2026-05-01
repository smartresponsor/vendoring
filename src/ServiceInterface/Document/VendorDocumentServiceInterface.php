<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Document;

use App\Vendoring\DTO\VendorDocumentDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorDocumentServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upload(VendorEntity $vendor, VendorDocumentDTO $dto): VendorDocumentEntity;
}
