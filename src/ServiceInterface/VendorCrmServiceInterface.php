<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Vendor;

interface VendorCrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void;
}
