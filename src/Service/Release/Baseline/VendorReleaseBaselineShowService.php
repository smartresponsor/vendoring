<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Release\Baseline;

use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Ops\VendorReleaseBaselineReaderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorReleaseBaselineShowService
{
    public function __construct(private VendorReleaseBaselineReaderServiceInterface $releaseBaselineReader, private VendorFinancialScopeResolverInterface $financialScope)
    {
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

        $scope = $this->financialScope->resolve($request, 'read:finance', $vendorId);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        if (ctype_digit($scope->getLedgerVendorId()) && $scope->getLedgerVendorId() !== (string) $scope->getVendor()->getId()) {
            return new JsonResponse(['error' => 'financial_identity_mismatch'], 403);
        }

        $projection = $this->releaseBaselineReader->build(
            tenantId: $scope->getTenantId(),
            vendorId: $scope->getLedgerVendorId(),
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            currency: (string) $request->query->get('currency', 'USD'),
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    private function attribute(Request $request, string $nameEntity): ?string
    {
        $value = $request->attributes->get($nameEntity);

        return is_scalar($value) && '' !== trim((string) $value) ? trim((string) $value) : null;
    }
}
