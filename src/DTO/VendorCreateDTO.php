<?php

declare(strict_types=1);

namespace App\Vendoring\DTO;

final readonly class VendorCreateDTO
{
    public function __construct(
        public string $brandName,
        public ?int $ownerUserId = null,
        public ?int $userId = null,
    ) {}

    public function resolveOwnerUserId(): ?int
    {
        return $this->ownerUserId ?? $this->userId;
    }
}
