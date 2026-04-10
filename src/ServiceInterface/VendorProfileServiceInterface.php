<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorProfileDTO;
use App\Entity\Vendor;
use App\Entity\VendorProfile;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorProfileServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile;
}
