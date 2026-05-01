<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Security;

use App\Vendoring\DTO\Security\VendorHmacVerificationDTO;
use App\Vendoring\ServiceInterface\Security\VendorHmacServiceInterface;

final class VendorHmacService implements VendorHmacServiceInterface
{
    public static function sign(string $payload, string $secret, string $algo = 'sha256'): string
    {
        return hash_hmac($algo, $payload, $secret);
    }

    public static function verify(VendorHmacVerificationDTO $verification): bool
    {
        $expected = self::sign($verification->payload, $verification->secret, $verification->algo);

        return hash_equals($expected, $verification->signature);
    }
}
