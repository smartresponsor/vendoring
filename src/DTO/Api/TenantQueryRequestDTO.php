<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\DTO\Api;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TenantQueryRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'tenant_id_required')]
        public string $tenantId,
    ) {}
}
