<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\ServiceInterface\VendorCrmServiceInterface;

final class VendorCrmService implements VendorCrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void
    {
        // CRM integration is disabled until a concrete provider is configured.
    }
}
