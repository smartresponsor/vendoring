<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Projection\VendorOwnershipView;

interface VendorOwnershipViewBuilderInterface
{
    public function buildForVendorId(int $vendorId): ?VendorOwnershipView;
}
