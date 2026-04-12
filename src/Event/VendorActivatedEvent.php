<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Vendor;

final readonly class VendorActivatedEvent
{
    public function __construct(public Vendor $vendor) {}
}
