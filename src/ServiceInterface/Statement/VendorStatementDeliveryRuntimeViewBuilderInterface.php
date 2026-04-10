<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Projection\VendorStatementDeliveryRuntimeView;

interface VendorStatementDeliveryRuntimeViewBuilderInterface
{
    public function build(VendorStatementDeliveryRuntimeRequestDTO $request): VendorStatementDeliveryRuntimeView;
}
