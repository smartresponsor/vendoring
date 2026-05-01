<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ownership;

use App\Vendoring\Projection\Vendor\VendorOwnershipProjection;

interface VendorOwnershipProjectionBuilderServiceInterface
{
    public function buildForVendorId(int $vendorId): ?VendorOwnershipProjection;
}
