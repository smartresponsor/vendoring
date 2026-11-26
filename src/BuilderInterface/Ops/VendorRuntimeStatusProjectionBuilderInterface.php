<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Ops;

use App\Vendoring\Projection\VendorRuntimeStatusProjection;

interface VendorRuntimeStatusProjectionBuilderInterface
{
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
        ?int $canonicalVendorId = null,
    ): VendorRuntimeStatusProjection;
}
