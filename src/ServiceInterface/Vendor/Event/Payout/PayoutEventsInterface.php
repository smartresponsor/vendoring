<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Event\Payout;

interface PayoutEventsInterface
{

    public function __construct(public string $payoutId, public string $vendorId);
}
