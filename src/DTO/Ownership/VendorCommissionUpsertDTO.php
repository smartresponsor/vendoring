<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * Write request for a vendor-owned commission definition.
 */
final readonly class VendorCommissionUpsertDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public int $vendorId,
        public string $code,
        public string $direction,
        public string $ratePercent,
        public ?string $status = 'active',
        public ?int $changedByUserId = null,
        public ?string $reason = null,
        public array $meta = [],
    ) {}
}
