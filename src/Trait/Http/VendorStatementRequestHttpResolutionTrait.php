<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Trait\Http;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ResolverInterface\Api\VendorStatementWindowQueryRequestResolverInterface;
use App\Vendoring\ResolverInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait VendorStatementRequestHttpResolutionTrait
{
    private function resolveStatementRequestOrErrorResponse(
        string $vendorId,
        Request $request,
        VendorStatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
        VendorStatementRequestResolverInterface $requestResolver,
    ): VendorStatementRequestDTO|JsonResponse {
        try {
            $statementWindowQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
        }

        try {
            $dto = $requestResolver->resolveStatementRequest($vendorId, $request);
        } catch (AccessDeniedHttpException) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }
        if (null === $dto) {
            return $this->validationErrorResponse(
                'statement_params_required',
                'Provide from and to query parameters.',
            );
        }

        return $dto;
    }
}
