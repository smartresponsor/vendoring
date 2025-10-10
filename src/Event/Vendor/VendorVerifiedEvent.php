<?php
declare(strict_types=1);

namespace App\Event\Vendor;

use App\Entity\Vendor\VendorPassport;

final class VendorVerifiedEvent
{
    public function __construct(public readonly VendorPassport $passport) {}
}
