<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * @param array<string, mixed> $meta
 */
final readonly class VendorCustomerOrderUpsertDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public int $vendorId,
        public string $externalOrderId,
        public string $status,
        public string $currency,
        public int $grossCents,
        public int $netCents,
        public ?string $orderNumber = null,
        public array $meta = [],
    ) {}
}
