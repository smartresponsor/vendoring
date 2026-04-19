<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorPassport;

final readonly class VendorVerifiedEvent
{
    public function __construct(public VendorPassport $passport) {}
}
