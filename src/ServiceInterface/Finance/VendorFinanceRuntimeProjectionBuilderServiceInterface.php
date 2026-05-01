<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Finance;

use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeProjection;
use Doctrine\DBAL\Exception;

interface VendorFinanceRuntimeProjectionBuilderServiceInterface
{
    /** @throws Exception */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorFinanceRuntimeProjection;
}
