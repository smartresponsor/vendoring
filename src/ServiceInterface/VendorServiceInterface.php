<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorCreateDTO;
use App\DTO\VendorUpdateDTO;
use App\Entity\Vendor;

/**
 * Application contract for vendor service operations.
 */
interface VendorServiceInterface
{
    /**
     * Creates the requested resource from the supplied input.
     */
    public function create(VendorCreateDTO $dto): Vendor;

    /**
     * Updates the requested resource state.
     */
    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor;
}
