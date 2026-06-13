<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Canary\Rollout;

use App\Vendoring\ServiceInterface\Rollout\VendorCanaryRolloutCoordinatorServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorCanaryRolloutOverviewService
{
    public function __construct(private VendorCanaryRolloutCoordinatorServiceInterface $canaryRolloutCoordinator)
    {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

        $flagName = trim((string) $request->query->get('flagName', ''));
        if ('' === $flagName) {
            return new JsonResponse([
                'error' => 'flag_name_required',
                'error_code' => 'flag_name_required',
            ], 400);
        }

        $windowSeconds = max(1, (int) $request->query->get('windowSeconds', 900));
        $tenantId = $request->query->has('tenantId') ? (string) $request->query->get('tenantId') : null;
        $vendorId = $request->query->has('vendorId') ? (string) $request->query->get('vendorId') : null;

        return new JsonResponse([
            'data' => $this->canaryRolloutCoordinator->evaluate($flagName, $tenantId, $vendorId, $windowSeconds),
        ], 200);
    }
}
