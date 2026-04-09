<?php

declare(strict_types=1);

namespace App\ServiceInterface\Integration;

use App\Projection\VendorExternalIntegrationRuntimeView;

interface VendorExternalIntegrationRuntimeViewBuilderInterface
{
    public function build(string $tenantId, string $vendorId): VendorExternalIntegrationRuntimeView;
}
