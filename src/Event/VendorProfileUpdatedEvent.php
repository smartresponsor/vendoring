<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorProfile;

final readonly class VendorProfileUpdatedEvent
{
    public function __construct(public VendorProfile $profile) {}
}
