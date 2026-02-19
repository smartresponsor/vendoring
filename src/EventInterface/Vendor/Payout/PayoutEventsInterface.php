<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\EventInterface\Vendor\Payout;

interface PayoutEventsInterface
{

    public function __construct(public string $payoutId, public string $vendorId);
}
