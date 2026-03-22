<?php

declare(strict_types=1);

namespace App\Service;

use App\EntityInterface\VendorSecurityInterface;
use App\Projection\VendorSecurityStateView;
use App\ServiceInterface\VendorSecurityStateViewBuilderInterface;

/**
 * Builds a lightweight read model for transitional vendor-local security state.
 */
final class VendorSecurityStateViewBuilder implements VendorSecurityStateViewBuilderInterface
{
    public function build(VendorSecurityInterface $security): VendorSecurityStateView
    {
        return new VendorSecurityStateView(
            vendorId: $security->getVendorId(),
            status: $security->getStatus(),
        );
    }
}
