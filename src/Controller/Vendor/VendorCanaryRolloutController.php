<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ServiceInterface\Rollout\VendorCanaryRolloutCoordinatorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-side operator endpoint for canary rollout readiness and next-step guidance.
 */
#[Route('/api/vendor-monitoring')]
final class VendorCanaryRolloutController extends AbstractController
{
    public function __construct(private readonly VendorCanaryRolloutCoordinatorServiceInterface $canaryRolloutCoordinator) {}

    /**
     * Render canary rollout status for one flag and runtime cohort.
     */
    #[Route('/canary-rollout', methods: ['GET'])]
    public function overview(Request $request): JsonResponse
    {
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
