<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Profile;

use App\Vendoring\Projection\Vendor\VendorProfileProjection;

interface VendorProfileProjectionBuilderServiceInterface
{
    public function buildForVendorId(int $vendorId): ?VendorProfileProjection;
}
