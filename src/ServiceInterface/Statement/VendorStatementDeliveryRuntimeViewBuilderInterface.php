<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\Projection\VendorStatementDeliveryRuntimeView;

interface VendorStatementDeliveryRuntimeViewBuilderInterface
{
    public function build(
        string $tenantId,
        string $vendorId,
        string $from,
        string $to,
        string $currency = 'USD',
        bool $includeExport = true,
    ): VendorStatementDeliveryRuntimeView;
}
