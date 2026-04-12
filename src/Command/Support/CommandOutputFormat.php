<?php

declare(strict_types=1);

namespace App\Command\Support;

final class CommandOutputFormat
{
    public const string TEXT = 'text';
    public const string JSON = 'json';

    public static function normalize(mixed $value): string
    {
        if (!is_scalar($value)) {
            return self::TEXT;
        }

        $normalized = strtolower(trim((string) $value));

        return self::JSON === $normalized ? self::JSON : self::TEXT;
    }

    public static function isJson(mixed $value): bool
    {
        return self::JSON === self::normalize($value);
    }
}
