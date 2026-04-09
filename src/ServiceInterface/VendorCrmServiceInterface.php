<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Vendor;

/**
 * Application contract for vendor crm service operations.
 */
interface VendorCrmServiceInterface
{
    /**
     * Executes the register vendor operation for this runtime surface.
     */
    public function registerVendor(Vendor $vendor): void;
}
