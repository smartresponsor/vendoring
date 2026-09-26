<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Security;

use App\Vendoring\BuilderInterface\Security\VendorSecurityStateProjectionBuilderInterface;
use App\Vendoring\EntityInterface\VendorSecurityEntityInterface;
use App\Vendoring\Projection\VendorSecurityStateProjection;

/**
 * Builds a lightweight read model for transitional vendor-local security state.
 */
final class VendorSecurityStateProjectionBuilder implements VendorSecurityStateProjectionBuilderInterface
{
    public function build(VendorSecurityEntityInterface $security): VendorSecurityStateProjection
    {
        return new VendorSecurityStateProjection(
            vendorId: $security->getVendorId(),
            status: $security->getStatus(),
        );
    }
}
