<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\EntityInterface\VendorSecurityInterface;
use App\Vendoring\Projection\VendorSecurityStateView;
use App\Vendoring\ServiceInterface\VendorSecurityStateViewBuilderInterface;

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
