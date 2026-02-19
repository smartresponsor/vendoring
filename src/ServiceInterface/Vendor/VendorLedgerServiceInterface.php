<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorLedgerBinding;

interface VendorLedgerServiceInterface
{
    public function createVendorAccount(Vendor $vendor): VendorLedgerBinding;
}
