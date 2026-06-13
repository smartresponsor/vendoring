<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Runtime\Finance;

use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorRuntimeFinanceShowService
{
    public function __construct(
        private VendorFinanceRuntimeProjectionBuilderServiceInterface $runtimeProjectionBuilder,
        private VendorTenantQueryRequestResolverServiceInterface $tenantQueryRequestResolver,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

        $vendorId = $this->attribute($request, 'id') ?? $this->attribute($request, 'slug') ?? $this->attribute($request, 'item') ?? (string) $request->query->get('vendorId', '');
        if ('' === $vendorId) {
            return new JsonResponse(['error' => 'vendor_identifier_required'], 422);
        }

        try {
            $tenantQuery = $this->tenantQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
        }

        $projection = $this->runtimeProjectionBuilder->build(
            $tenantQuery->tenantId,
            $vendorId,
            $request->query->get('from') ? (string) $request->query->get('from') : null,
            $request->query->get('to') ? (string) $request->query->get('to') : null,
            (string) ($request->query->get('currency') ?? 'USD'),
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    private function attribute(Request $request, string $nameEntity): ?string
    {
        $value = $request->attributes->get($nameEntity);

        return is_scalar($value) && '' !== trim((string) $value) ? trim((string) $value) : null;
    }

    private function validationErrorResponse(string $errorCode, string $hint): JsonResponse
    {
        return new JsonResponse(['error' => $errorCode, 'hint' => $hint], 422);
    }
}
