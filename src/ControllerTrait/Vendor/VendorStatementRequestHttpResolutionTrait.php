<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ControllerTrait\Vendor;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait VendorStatementRequestHttpResolutionTrait
{
    private function resolveStatementRequestOrErrorResponse(
        string $vendorId,
        Request $request,
        VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
        VendorStatementRequestResolverServiceInterface $requestResolver,
    ): VendorStatementRequestDTO|JsonResponse {
        try {
            $statementWindowQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
        }

        $dto = $requestResolver->resolveStatementRequest($vendorId, $request);
        if (null === $dto) {
            return $this->validationErrorResponse(
                'statement_params_required',
                'Provide tenantId, from, and to query parameters.',
            );
        }

        return $dto;
    }
}
