<?php

declare(strict_types=1);

namespace App\ServiceInterface\Integration;

use App\Projection\VendorExternalIntegrationRuntimeView;

/**
 * Contract for building vendor external integration runtime view builder views.
 */
interface VendorExternalIntegrationRuntimeViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeView;
}
