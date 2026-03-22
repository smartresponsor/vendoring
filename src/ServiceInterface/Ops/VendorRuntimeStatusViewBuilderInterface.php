<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

use App\Projection\VendorRuntimeStatusView;

interface VendorRuntimeStatusViewBuilderInterface
{
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorRuntimeStatusView;
}
