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
     * @var array<string, string>
     */
    private const LABELS = [
        self::PENDING => 'Pending',
        self::AUTHORIZED => 'Authorized',
        self::SETTLED => 'Settled',
        self::FAILED => 'Failed',
        self::CANCELLED => 'Cancelled',
        self::REFUNDED => 'Refunded',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::LABELS);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return self::LABELS;
    }

    /**
     * @return array<string, string>
     */
    public static function operatorChoices(): array
    {
        $choices = [];

        foreach (self::LABELS as $value => $label) {
            $choices[$label] = $value;
        }

        return $choices;
    }

    public static function label(string $status): string
    {
        $normalized = strtolower(trim($status));

        return self::LABELS[$normalized] ?? ucfirst($normalized);
    }
}
