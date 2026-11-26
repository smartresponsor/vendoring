<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Runtime\ExternalIntegration;

use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorRuntimeExternalIntegrationShowService
{
    public function __construct(
        private VendorExternalIntegrationRuntimeProjectionBuilderInterface $runtimeProjectionBuilder,
        private VendorFinancialScopeResolverInterface $financialScope,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

        $vendorId = $this->attribute($request, 'id') ?? $this->attribute($request, 'slug') ?? $this->attribute($request, 'item') ?? (string) $request->query->get('vendorId', '');
        if ('' === $vendorId) {
            return $this->validationErrorResponse('vendor_identifier_required', 'Provide id, slug, item, or vendorId.');
        }

        $scope = $this->financialScope->resolve($request, 'read:finance', $vendorId);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        $projection = $this->runtimeProjectionBuilder->build($scope->getLedgerVendorId(), $scope->getVendor()->getId());

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
