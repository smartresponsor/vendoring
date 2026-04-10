<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Sec;

use App\DTO\Sec\HmacVerificationDTO;

interface HmacInterface
{
    public static function sign(string $payload, string $secret, string $algo = 'sha256'): string;

    public static function verify(HmacVerificationDTO $verification): bool;
}
