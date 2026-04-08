<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Vendor;
use App\ServiceInterface\VendorCrmServiceInterface;

/**
 * Application service for vendor crm operations.
 */
final class VendorCrmService implements VendorCrmServiceInterface
{
    /**
     * Executes the register vendor operation for this runtime surface.
     */
    public function registerVendor(Vendor $vendor): void
    {
        // CRM integration is disabled until a concrete provider is configured.
    }
}
