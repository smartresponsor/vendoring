<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Statement;

final readonly class VendorStatementDeliveryRuntimeRequestDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $from,
        public string $to,
        public string $currency = 'USD',
        public bool $includeExport = true,
        public ?int $canonicalVendorId = null,
    ) {
        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
    }
}
