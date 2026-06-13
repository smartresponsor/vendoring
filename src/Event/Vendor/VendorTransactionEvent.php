<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @noinspection PhpConstantNamingConventionInspection
 */
final class VendorTransactionEvent extends Event
{
    public const string EVENT_NAME = 'vendor.transaction.action';
    public const string NAME = self::EVENT_NAME;

    public function __construct(public readonly VendorTransactionEntity $transaction) {}
}
