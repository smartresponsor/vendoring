<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Integration;

use App\Vendoring\Projection\VendorExternalIntegrationRuntimeProjection;

interface VendorExternalIntegrationRuntimeProjectionBuilderInterface
{
    public function build(string $vendorId): VendorExternalIntegrationRuntimeProjection;
}
