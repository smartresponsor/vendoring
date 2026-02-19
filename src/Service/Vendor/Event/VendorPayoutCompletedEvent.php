<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Event;

use App\Entity\Vendor\VendorBilling;

final class VendorPayoutCompletedEvent
{
    public function __construct(public readonly VendorBilling $billing, public readonly int $amountMinor = 0)
    {
    }
}
