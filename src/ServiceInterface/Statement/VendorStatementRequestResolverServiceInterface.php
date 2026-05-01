<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface VendorStatementRequestResolverServiceInterface
{
    public function resolveStatementRequest(string $vendorId, Request $request): ?VendorStatementRequestDTO;

    public function resolveDeliveryRuntimeRequest(string $vendorId, Request $request): ?VendorStatementDeliveryRuntimeRequestDTO;
}
