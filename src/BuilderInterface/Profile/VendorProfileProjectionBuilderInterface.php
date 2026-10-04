<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Profile;

use App\Vendoring\Projection\VendorProfileProjection;

interface VendorProfileProjectionBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorProfileProjection;
}
