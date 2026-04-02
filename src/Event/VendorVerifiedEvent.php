<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorPassport;

final class VendorVerifiedEvent
{
    public function __construct(public readonly VendorPassport $passport)
    {
    }
}
