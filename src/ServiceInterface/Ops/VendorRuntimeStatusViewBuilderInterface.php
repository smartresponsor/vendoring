<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

use App\Projection\VendorRuntimeStatusView;

/**
 * Contract for building vendor runtime status view builder views.
 */
interface VendorRuntimeStatusViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorRuntimeStatusView;
}
