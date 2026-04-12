<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class VendorUpdateDTO
{
    public function __construct(
        public ?string $brandName = null,
        public ?string $status = null,
        public ?int $ownerUserId = null,
        public ?int $userId = null,
    ) {}

    public function resolveOwnerUserId(): ?int
    {
        return $this->ownerUserId ?? $this->userId;
    }
}
