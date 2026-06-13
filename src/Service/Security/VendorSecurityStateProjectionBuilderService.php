<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Security;

use App\Vendoring\EntityInterface\Vendor\VendorSecurityEntityInterface;
use App\Vendoring\Projection\Vendor\VendorSecurityStateProjection;
use App\Vendoring\ServiceInterface\Security\VendorSecurityStateProjectionBuilderServiceInterface;

/**
 * Builds a lightweight read model for transitional vendor-local security state.
 */
final class VendorSecurityStateProjectionBuilderService implements VendorSecurityStateProjectionBuilderServiceInterface
{
    public function build(VendorSecurityEntityInterface $security): VendorSecurityStateProjection
    {
        return new VendorSecurityStateProjection(
            vendorId: $security->getVendorId(),
            status: $security->getStatus(),
        );
    }
}
