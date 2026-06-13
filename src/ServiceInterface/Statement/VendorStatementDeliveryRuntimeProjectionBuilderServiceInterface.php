<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeProjection;

interface VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface
{
    public function build(VendorStatementDeliveryRuntimeRequestDTO $request): VendorStatementDeliveryRuntimeProjection;
}
