<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Projection\VendorOwnershipView;

/**
 * Contract for building vendor ownership view builder views.
 */
interface VendorOwnershipViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function buildForVendorId(int $vendorId): ?VendorOwnershipView;
}
