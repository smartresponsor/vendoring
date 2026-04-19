<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Projection\VendorProfileView;

interface VendorProfileViewBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorProfileView;
}
