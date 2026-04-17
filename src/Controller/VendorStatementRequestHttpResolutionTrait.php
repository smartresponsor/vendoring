<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\Exception\ApiQueryValidationException;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait VendorStatementRequestHttpResolutionTrait
{
    private function resolveStatementRequestOrErrorResponse(
        string $vendorId,
        Request $request,
        StatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
        VendorStatementRequestResolverInterface $requestResolver,
    ): VendorStatementRequestDTO|JsonResponse {
        try {
            $statementWindowQueryRequestResolver->resolve($request);
        } catch (ApiQueryValidationException $exception) {
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
