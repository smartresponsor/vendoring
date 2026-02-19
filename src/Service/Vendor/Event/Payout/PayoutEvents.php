<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Event\Payout;

use App\ServiceInterface\Vendor\Event\Payout\PayoutCreatedEventInterface;

final class PayoutCreatedEvent implements PayoutCreatedEventInterface
{
    public function __construct(public string $payoutId, public string $vendorId)
    {
    }
}

final class PayoutProcessedEvent
{
    public function __construct(public string $payoutId, public string $vendorId)
    {
    }
}
