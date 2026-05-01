<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Integration;

use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorCrmServiceInterface
{
    public function registerVendor(VendorEntity $vendor): void;
}
