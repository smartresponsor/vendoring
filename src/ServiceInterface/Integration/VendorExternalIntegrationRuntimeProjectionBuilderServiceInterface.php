<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Integration;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;

interface VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface
{
    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeProjection;
}
