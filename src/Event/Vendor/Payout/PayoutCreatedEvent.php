<?php

declare(strict_types=1);

namespace App\Event\Vendor\Payout;

final class PayoutCreatedEvent
{
    public function __construct(public string $payoutId, public string $vendorId)
    {
    }
}
