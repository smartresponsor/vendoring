<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * Write request for a vendor-owned shipment link.
 */
final readonly class VendorShipmentUpsertDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public int $vendorId,
        public ?string $externalShipmentId = null,
        public ?string $carrierCode = null,
        public ?string $methodCode = null,
        public ?string $trackingNumber = null,
        public ?string $status = 'pending',
        public array $meta = [],
    ) {}
}
