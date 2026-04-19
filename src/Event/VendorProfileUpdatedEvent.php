<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorProfile;

final readonly class VendorProfileUpdatedEvent
{
    public function __construct(public VendorProfile $profile) {}
}
