<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Event;

use App\Entity\Vendor\VendorProfile;

final class VendorProfileUpdatedEvent
{
    public function __construct(public readonly VendorProfile $profile)
    {
    }
}
