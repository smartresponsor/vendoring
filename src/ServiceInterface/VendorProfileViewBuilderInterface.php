<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Projection\VendorProfileView;

interface VendorProfileViewBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorProfileView;
}
