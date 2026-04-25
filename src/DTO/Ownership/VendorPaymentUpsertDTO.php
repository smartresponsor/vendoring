<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * Write request for a vendor-owned payment method binding.
 */
final readonly class VendorPaymentUpsertDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public int $vendorId,
        public string $providerCode,
        public string $methodCode,
        public ?string $externalPaymentId = null,
        public ?string $label = null,
        public ?string $status = 'active',
        public ?bool $isDefault = null,
        public array $meta = [],
    ) {}
}
