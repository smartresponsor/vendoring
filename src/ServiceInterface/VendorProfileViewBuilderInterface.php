<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Projection\VendorProfileView;

/**
 * Contract for building vendor profile view builder views.
 */
interface VendorProfileViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function buildForVendorId(int $vendorId): ?VendorProfileView;
}
