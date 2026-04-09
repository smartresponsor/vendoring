<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorProfileDTO;
use App\Entity\Vendor;
use App\Entity\VendorProfile;

/**
 * Application contract for vendor profile service operations.
 */
interface VendorProfileServiceInterface
{
    /**
     * Creates or updates the requested aggregate state.
     */
    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile;
}
