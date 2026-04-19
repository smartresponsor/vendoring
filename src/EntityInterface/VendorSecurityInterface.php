<?php

declare(strict_types=1);

namespace App\Vendoring\EntityInterface;

use App\Vendoring\Entity\Vendor;

/**
 * Transitional state contract for vendor-local security status.
 *
 * This contract intentionally does not represent external human credentials or
 * Symfony user authentication. Vendoring only owns a lightweight local state.
 */
interface VendorSecurityInterface
{
    public function getId(): ?int;

    public function getVendor(): Vendor;

    public function getVendorId(): ?int;

    public function getStatus(): string;
}
