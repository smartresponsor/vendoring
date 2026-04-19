<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\Vendor;

final readonly class VendorActivatedEvent
{
    public function __construct(public Vendor $vendor) {}
}
