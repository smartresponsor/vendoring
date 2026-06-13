<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Profile;

use App\Vendoring\DTO\VendorProfileDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorProfileServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsert(VendorEntity $vendor, VendorProfileDTO $dto): VendorProfileEntity;
}
