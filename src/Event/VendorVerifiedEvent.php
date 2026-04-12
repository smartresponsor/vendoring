<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorPassport;

final readonly class VendorVerifiedEvent
{
    public function __construct(public VendorPassport $passport) {}
}
