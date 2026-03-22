<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Projection\VendorOwnershipView;

interface VendorOwnershipViewBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorOwnershipView;
}
