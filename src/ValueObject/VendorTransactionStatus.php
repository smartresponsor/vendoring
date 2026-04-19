<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

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
            self::SETTLED,
            self::FAILED,
            self::CANCELLED,
            self::REFUNDED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function operatorChoices(): array
    {
        return [
            'Pending' => self::PENDING,
            'Authorized' => self::AUTHORIZED,
            'Settled' => self::SETTLED,
            'Failed' => self::FAILED,
            'Cancelled' => self::CANCELLED,
            'Refunded' => self::REFUNDED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::operatorChoices() as $label => $status) {
            $labels[$status] = $label;
        }

        return $labels;
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? ucfirst($status);
    }
}
