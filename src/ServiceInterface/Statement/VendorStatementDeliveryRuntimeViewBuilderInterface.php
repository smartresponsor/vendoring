<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\Projection\VendorStatementDeliveryRuntimeView;

/**
 * Contract for building vendor statement delivery runtime view builder views.
 */
interface VendorStatementDeliveryRuntimeViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        string $from,
        string $to,
        string $currency = 'USD',
        bool $includeExport = true,
    ): VendorStatementDeliveryRuntimeView;
}
