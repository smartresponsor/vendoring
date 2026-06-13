<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorPassportEntity;

final readonly class VendorVerifiedEvent
{
    public function __construct(public VendorPassportEntity $passport) {}
}
