<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Projection\VendorFinanceRuntimeView;

/**
 * Contract for building vendor finance runtime view builder views.
 */
interface VendorFinanceRuntimeViewBuilderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorFinanceRuntimeView;
}
