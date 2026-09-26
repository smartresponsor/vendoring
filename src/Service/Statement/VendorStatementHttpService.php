<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\ResolverInterface\Api\VendorStatementWindowQueryRequestResolverInterface;
use App\Vendoring\ResolverInterface\Statement\VendorStatementRequestResolverInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\Trait\Http\VendorApiErrorResponseTrait;
use App\Vendoring\Trait\Http\VendorStatementRequestHttpResolutionTrait;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementHttpService
{
    use VendorApiErrorResponseTrait;
    use VendorStatementRequestHttpResolutionTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
    ) {
    }

    /** @throws Exception */
    public function build(string $id, Request $r): JsonResponse
    {
        $dto = $this->resolveStatementRequestOrErrorResponse(
            $id,
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
