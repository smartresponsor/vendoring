<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Statement\Delivery\Runtime;

use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ResolverInterface\Api\VendorStatementWindowQueryRequestResolverInterface;
use App\Vendoring\ResolverInterface\Statement\VendorStatementRequestResolverInterface;
use App\Vendoring\Trait\Http\VendorApiErrorResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementDeliveryRuntimeService
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private readonly VendorStatementDeliveryRuntimeProjectionBuilderInterface $runtimeProjectionBuilder,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
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
                'Provide from and to query parameters.',
            );
        }

        $projection = $this->runtimeProjectionBuilder->build($runtimeRequest);

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}
