<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;

final readonly class VendorPayoutRequestedEvent
{
    public function __construct(public VendorBillingEntity $billing, public int $amountMinor = 0) {}
}
