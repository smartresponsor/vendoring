<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Finance;

use App\Vendoring\Projection\VendorFinanceRuntimeProjection;
use Doctrine\DBAL\Exception;

interface VendorFinanceRuntimeProjectionBuilderInterface
{
    /** @throws Exception */
    public function build(
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorFinanceRuntimeProjection;
}
