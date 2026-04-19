<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\VendorProfileDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorProfile;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorProfileServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile;
}
