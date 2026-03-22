<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

use App\EntityInterface\VendorSecurityEntityInterface;

/**
 * Transitional vendor-owned security state.
 *
 * This entity is not the human identity/authentication model of Vendoring.
 * It only reflects lightweight vendor-local state while canonical machine
 * access lives in VendorApiKey and external human credentials remain outside
 * this boundary.
 */
final class VendorSecurity implements VendorSecurityEntityInterface
{
    private ?int $id = null;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $status = 'active',
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getVendorId(): ?int
    {
        return $this->vendor->getId();
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
