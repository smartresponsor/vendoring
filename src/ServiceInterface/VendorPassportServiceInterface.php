<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\Vendor;
use App\Entity\VendorPassport;

/**
 * Application contract for vendor passport service operations.
 */
interface VendorPassportServiceInterface
{
    /**
     * Determines whether the requested condition is satisfied.
     */
    public function issue(Vendor $vendor, string $taxId, string $country): VendorPassport;

    /**
     * Executes the verify operation for this runtime surface.
     */
    public function verify(VendorPassport $passport): VendorPassport;
}
