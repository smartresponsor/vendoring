<?php

declare(strict_types=1);

namespace App\DTO\Statement;

final readonly class VendorStatementRecipientDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $email,
        public string $currency = 'USD',
    ) {
    }
}
