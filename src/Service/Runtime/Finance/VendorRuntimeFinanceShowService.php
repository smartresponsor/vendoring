<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Runtime\Finance;

use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\Trait\Http\VendorApiErrorResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorRuntimeFinanceShowService
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private VendorFinanceRuntimeProjectionBuilderInterface $runtimeProjectionBuilder,
        private VendorFinancialScopeResolverInterface $financialScope,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $vendorId = $this->attribute($request, 'id') ?? $this->attribute($request, 'slug') ?? $this->attribute($request, 'item') ?? (string) $request->query->get('vendorId', '');
        if ('' === $vendorId) {
            return new JsonResponse(['error' => 'vendor_identifier_required'], 422);
        }

        $scope = $this->financialScope->resolve($request, 'read:finance', $vendorId);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        if (ctype_digit($scope->getLedgerVendorId()) && $scope->getLedgerVendorId() !== (string) $scope->getVendor()->getId()) {
            return new JsonResponse(['error' => 'financial_identity_mismatch'], 403);
        }

        $projection = $this->runtimeProjectionBuilder->build(
            $scope->getTenantId(),
            $scope->getLedgerVendorId(),
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
}
