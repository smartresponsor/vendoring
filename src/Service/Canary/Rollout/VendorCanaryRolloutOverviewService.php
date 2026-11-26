<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Canary\Rollout;

use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Rollout\VendorCanaryRolloutCoordinatorServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[\Symfony\Component\HttpKernel\Attribute\AsController]
final readonly class VendorCanaryRolloutOverviewService
{
    public function __construct(private VendorCanaryRolloutCoordinatorServiceInterface $canaryRolloutCoordinator, private VendorFinancialScopeResolverInterface $financialScope)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $flagName = trim((string) $request->query->get('flagName', ''));
        if ('' === $flagName) {
            return new JsonResponse([
                'error' => 'flag_name_required',
                'error_code' => 'flag_name_required',
            ], 400);
        }

        $windowSeconds = max(1, (int) $request->query->get('windowSeconds', 900));
        $scope = $this->financialScope->resolve($request, 'read:rollout');
        if (null === $scope || null === $scope->getTenantId() || null === $scope->getVendor()->getId() || 'active' !== $scope->getVendor()->getStatus()) {
            return new JsonResponse(['error' => 'rollout_scope_denied'], 403);
        }
        $report = $this->canaryRolloutCoordinator->evaluate($flagName, (string) $scope->getVendor()->getId(), $windowSeconds, $scope->getTenantId());

        // The vendor-facing endpoint is not a global release/operations dashboard.
        return new JsonResponse(['data' => [
            'tenantId' => $scope->getTenantId(),
            'vendorId' => (string) $scope->getVendor()->getId(),
            'flagDecision' => $report['flagDecision'],
            'canary' => $report['canary'],
        ]], 200);
    }
}
