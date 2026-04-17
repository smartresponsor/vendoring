<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityInterface\VendorSecurityEntityInterface;

/**
 * Transitional vendor-owned security state.
 *
 * This entity is not the human identity/authentication model of Vendoring.
 * It only reflects lightweight vendor-local state while canonical machine
 * access lives in VendorApiKey and external human credentials remain outside
 * this boundary.
 */
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorSecurity implements VendorSecurityEntityInterface
{
    /** @var int|null */
    // @phpstan-ignore-next-line
    private ?int $id = null;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $status = 'active',
    ) {}

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
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
