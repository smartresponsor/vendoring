<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Sec;

use App\DTO\Sec\HmacVerificationDTO;
use App\ServiceInterface\Sec\HmacInterface;

final class Hmac implements HmacInterface
{
    public static function sign(string $payload, string $secret, string $algo = 'sha256'): string
    {
        return hash_hmac($algo, $payload, $secret);
    }

    public static function verify(HmacVerificationDTO $verification): bool
    {
        $expected = self::sign($verification->payload, $verification->secret, $verification->algo);

        return hash_equals($expected, $verification->signature);
    }
}
