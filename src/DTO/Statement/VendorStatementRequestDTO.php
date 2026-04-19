<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Statement;

final class VendorStatementRequestDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $from,   // Y-m-d
        public string $to,     // Y-m-d
        public string $currency,
    ) {}
}
