<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;

final readonly class VendorCreatedEvent
{
    public function __construct(public VendorEntity $vendor) {}
}
