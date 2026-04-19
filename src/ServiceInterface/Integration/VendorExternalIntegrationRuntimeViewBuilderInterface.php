<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Integration;

use App\Vendoring\Projection\VendorExternalIntegrationRuntimeView;

interface VendorExternalIntegrationRuntimeViewBuilderInterface
{
    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeView;
}
