<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor;

use App\Entity\Vendor\Vendor;

interface VendorProductCatalogServiceInterface
{
    public function assignToVendor(Vendor $vendor): void;
}
