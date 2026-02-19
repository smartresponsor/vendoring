<?php
declare(strict_types = 1);

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\ServiceInterface\Vendor\CrmServiceInterface;

final class CrmService
    implements CrmServiceInterface
{
    public function registerVendor(Vendor $vendor): void
    {
        // placeholder: call CRM API / write record
    }
}
