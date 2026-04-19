<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorBilling;

final readonly class VendorPayoutRequestedEvent
{
    public function __construct(public VendorBilling $billing, public int $amountMinor = 0) {}
}
