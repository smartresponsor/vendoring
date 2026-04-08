<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EntityInterface\VendorSecurityInterface;
use App\Projection\VendorSecurityStateView;

/**
 * Contract for building vendor security state view builder views.
 */
interface VendorSecurityStateViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(VendorSecurityInterface $security): VendorSecurityStateView;
}
