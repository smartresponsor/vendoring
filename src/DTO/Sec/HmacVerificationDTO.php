<?php

declare(strict_types=1);

namespace App\DTO\Sec;

final readonly class HmacVerificationDTO
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
