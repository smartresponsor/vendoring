<?php
declare(strict_types = 1);

namespace App\Service\Sec;
final class Hmac
{
    public static function sign(string $payload, string $secret, string $algo = 'sha256'): string
    {
        return hash_hmac($algo, $payload, $secret);
    }

    public static function verify(string $payload, string $secret, string $signature, string $algo = 'sha256', int $leeway = 300, ?int $timestamp = null): bool
    {
        $expected = self::sign($payload, $secret, $algo);
        return hash_equals($expected, $signature);
    }
}
