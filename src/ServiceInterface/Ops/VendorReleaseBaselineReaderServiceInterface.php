<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ops;

use App\Vendoring\Projection\VendorReleaseBaselineProjection;

interface VendorReleaseBaselineReaderServiceInterface
{
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
        ?int $canonicalVendorId = null,
    ): VendorReleaseBaselineProjection;
}
