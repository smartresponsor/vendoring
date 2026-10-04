<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\VendorStatementDeliveryRuntimeProjection;

interface VendorStatementDeliveryRuntimeProjectionBuilderInterface
{
    public function build(VendorStatementDeliveryRuntimeRequestDTO $request): VendorStatementDeliveryRuntimeProjection;
}
