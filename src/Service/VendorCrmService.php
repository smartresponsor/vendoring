<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Vendor\Vendor;
use App\ServiceInterface\VendorCrmServiceInterface;

final class VendorCrmService implements VendorCrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void
    {
        // CRM integration is disabled until a concrete provider is configured.
    }
}
