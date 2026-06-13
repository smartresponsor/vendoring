<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Statement;

use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\Support\Http\VendorApiErrorResponseTrait;
use App\Vendoring\Support\Http\VendorStatementRequestHttpResolutionTrait;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementHttpService
{
    use VendorApiErrorResponseTrait;
    use VendorStatementRequestHttpResolutionTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverServiceInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
    ) {
    }

    /** @throws Exception */
    public function build(string $vendorId, Request $r): JsonResponse
    {
        $dto = $this->resolveStatementRequestOrErrorResponse(
            $vendorId,
            $r,
            $this->statementWindowQueryRequestResolver,
            $this->requestResolver,
        );
        if ($dto instanceof JsonResponse) {
            return $dto;
        }

        $data = $this->svc->build($dto);

        return new JsonResponse(['data' => $data], 200);
    }
}
