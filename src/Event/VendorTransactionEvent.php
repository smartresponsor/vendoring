<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Event;

use App\Entity\Vendor\VendorTransaction;
use Symfony\Contracts\EventDispatcher\Event;

final class VendorTransactionEvent extends Event
{
    public const NAME = 'vendor.transaction.action';

    public function __construct(public readonly VendorTransaction $transaction)
    {
    }
}
