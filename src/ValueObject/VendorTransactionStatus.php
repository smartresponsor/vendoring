<?php

declare(strict_types=1);

namespace App\ValueObject;

final class VendorTransactionStatus
{
    public const string PENDING = 'pending';
    public const string AUTHORIZED = 'authorized';
    public const string FAILED = 'failed';
    public const string CANCELLED = 'cancelled';
    public const string SETTLED = 'settled';
    public const string REFUNDED = 'refunded';

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
