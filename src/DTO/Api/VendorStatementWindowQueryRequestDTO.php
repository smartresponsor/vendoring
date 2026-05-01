<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\DTO\Api;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VendorStatementWindowQueryRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'tenant_id_required')]
        public string $tenantId,
        #[Assert\NotBlank(message: 'statement_from_required')]
        public string $from,
        #[Assert\NotBlank(message: 'statement_to_required')]
        public string $to,
        public string $currency = 'USD',
    ) {}
}
