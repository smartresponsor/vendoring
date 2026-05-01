<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Security;

use App\Vendoring\DTO\Security\VendorHmacVerificationDTO;

interface VendorHmacServiceInterface
{
    public static function sign(string $payload, string $secret, string $algo = 'sha256'): string;

    public static function verify(VendorHmacVerificationDTO $verification): bool;
}
