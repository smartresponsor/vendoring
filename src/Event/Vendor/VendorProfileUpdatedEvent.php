<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorProfileEntity;

final readonly class VendorProfileUpdatedEvent
{
    public function __construct(public VendorProfileEntity $profile) {}
}
