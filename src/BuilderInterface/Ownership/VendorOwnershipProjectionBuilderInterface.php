<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Ownership;

use App\Vendoring\Projection\VendorOwnershipProjection;

interface VendorOwnershipProjectionBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorOwnershipProjection;
}
