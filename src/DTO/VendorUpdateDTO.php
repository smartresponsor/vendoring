<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VendorUpdateDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'brand_name_required', allowNull: true, normalizer: 'trim')]
        #[Assert\Length(max: 255, maxMessage: 'brand_name_too_long')]
        public ?string $brandName = null,
        #[Assert\Choice(choices: ['active', 'inactive'], message: 'status_invalid')]
        public ?string $status = null,
        #[Assert\Positive(message: 'owner_user_id_must_be_positive')]
        public ?int $ownerUserId = null,
        #[Assert\Positive(message: 'user_id_must_be_positive')]
        public ?int $userId = null,
    ) {
    }

    public function resolveOwnerUserId(): ?int
    {
        return $this->ownerUserId ?? $this->userId;
    }
}
