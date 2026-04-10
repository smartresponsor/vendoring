<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface VendorStatementRequestResolverInterface
{
    public function resolveStatementRequest(string $vendorId, Request $request): ?VendorStatementRequestDTO;

    public function resolveDeliveryRuntimeRequest(string $vendorId, Request $request): ?VendorStatementDeliveryRuntimeRequestDTO;
}
