<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Entity\Vendor;

interface VendorCrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void;
}
