<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\VendorDocumentDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorDocument;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorDocumentServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument;
}
