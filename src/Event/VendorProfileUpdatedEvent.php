<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorProfile;

final class VendorProfileUpdatedEvent
{
    public function __construct(public readonly VendorProfile $profile)
    {
    }
}
