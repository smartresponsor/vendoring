<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Security;

final readonly class VendorHmacVerificationDTO
{
    public function __construct(
        public string $payload,
        public string $secret,
        public string $signature,
        public string $algo = 'sha256',
        public int $leeway = 300,
        public ?int $timestamp = null,
    ) {}
}
