<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Integration;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;

final class VendorCrmService implements VendorCrmServiceInterface
{
    public function registerVendor(VendorEntity $vendor): void
    {
        // CRM integration is disabled until a concrete provider is configured.
    }
}
