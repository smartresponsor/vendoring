<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

final class VendorPayoutCreatedEvent
{
    public function __construct(public string $payoutId, public string $vendorId)
    {
    }
}
