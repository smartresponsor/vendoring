<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorBilling;

final readonly class VendorPayoutCompletedEvent
{
    public function __construct(public VendorBilling $billing, public int $amountMinor = 0)
    {
    }
}
