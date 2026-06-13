<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Read-side representation of vendor-local security state.
 *
 * This projection exists to make the transitional VendorSecurityEntity entity easier
 * to read without implying that Vendoring owns full human-auth security.
 */
final readonly class VendorSecurityStateProjection
{
    public function __construct(
        public ?int $vendorId,
        public string $status,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'status' => $this->status,
        ];
    }
}
