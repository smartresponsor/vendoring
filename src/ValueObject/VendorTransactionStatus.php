<?php

declare(strict_types=1);

namespace App\ValueObject;

final class VendorTransactionStatus
{
    public const PENDING = 'pending';
    public const AUTHORIZED = 'authorized';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';
    public const SETTLED = 'settled';
    public const REFUNDED = 'refunded';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::AUTHORIZED,
            self::FAILED,
            self::CANCELLED,
            self::SETTLED,
            self::REFUNDED,
        ];
    }
}
