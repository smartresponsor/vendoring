<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Statement\Delivery\Runtime;

use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use App\Vendoring\Support\Http\VendorApiErrorResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementDeliveryRuntimeService
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private readonly VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface $runtimeProjectionBuilder,
        private readonly VendorStatementRequestResolverServiceInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
    ) {
    }

    public function show(string $vendorId, Request $request): JsonResponse
    {
        try {
            $this->statementWindowQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
        }

        $runtimeRequest = $this->requestResolver->resolveDeliveryRuntimeRequest($vendorId, $request);
        if (null === $runtimeRequest) {
            return $this->validationErrorResponse(
                'statement_runtime_params_required',
                'Provide tenantId, from, and to query parameters.',
            );
        }

        $projection = $this->runtimeProjectionBuilder->build($runtimeRequest);

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}
