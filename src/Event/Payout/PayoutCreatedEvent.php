<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Payout;

final class PayoutCreatedEvent
{
    public function __construct(public string $payoutId, public string $vendorId) {}
}
