<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Vendor\Vendor;

final class VendorActivatedEvent
{
    public function __construct(public readonly Vendor $vendor)
    {
    }
}
