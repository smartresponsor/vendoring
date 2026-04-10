<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorDocumentDTO;
use App\Entity\Vendor;
use App\Entity\VendorDocument;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorDocumentServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument;
}
