<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Vendor\VendorBilling;

final class VendorPayoutRequestedEvent
{
    public function __construct(public readonly VendorBilling $billing, public readonly int $amountMinor = 0)
    {
    }
}
