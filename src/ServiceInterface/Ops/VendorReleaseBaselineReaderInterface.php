<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

use App\Projection\VendorReleaseBaselineView;

interface VendorReleaseBaselineReaderInterface
{
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorReleaseBaselineView;
}
