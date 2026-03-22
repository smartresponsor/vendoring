<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Vendor\Vendor;

interface CrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void;
}
