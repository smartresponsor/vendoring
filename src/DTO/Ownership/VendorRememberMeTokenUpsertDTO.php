<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

final readonly class VendorRememberMeTokenUpsertDTO
{
    public function __construct(
        public int $vendorId,
        public string $series,
        public string $tokenValue,
        public string $providerClass,
        public string $username,
    ) {}
}
