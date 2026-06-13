<?php

declare(strict_types=1);

namespace App\Vendoring\EntityInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;

/**
 * Transitional state contract for vendor-local security status.
 *
 * This contract intentionally does not represent external human credentials or
 * Symfony user authentication. Vendoring only owns a lightweight local state.
 */
interface VendorSecurityEntityInterface
{
    public function getId(): ?int;

    public function getVendor(): VendorEntity;

    public function getVendorId(): ?int;

    public function getStatus(): string;
}
