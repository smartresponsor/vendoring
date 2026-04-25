<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

final readonly class VendorCodeStorageUpsertDTO
{
    public function __construct(
        public int $vendorId,
        public string $code,
        public string $purpose,
        public string $expiresAt,
        public ?string $phone = null,
        public bool $isLogin = false,
    ) {}
}
