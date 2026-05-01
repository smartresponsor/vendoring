<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ServiceInterface\Observability\VendorAlertRuleEvaluatorServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorMonitoringSnapshotBuilderServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-side operator endpoint for monitoring and alert overview.
 */
#[Route('/api/vendor-monitoring')]
final class VendorMonitoringController extends AbstractController
{
    public function __construct(
        private readonly VendorMonitoringSnapshotBuilderServiceInterface $snapshotBuilder,
        private readonly VendorAlertRuleEvaluatorServiceInterface $alertRuleEvaluator,
    ) {}

    /**
     * Render the current monitoring snapshot and evaluated alerts.
     */
    #[Route('/overview', methods: ['GET'])]
    public function overview(Request $request): JsonResponse
    {
        $windowSeconds = max(1, (int) $request->query->get('windowSeconds', 900));
        $snapshot = $this->snapshotBuilder->build($windowSeconds);
        $alerts = $this->alertRuleEvaluator->evaluate($snapshot);

        return new JsonResponse([
            'data' => [
                'snapshot' => $snapshot,
                'alerts' => $alerts,
            ],
        ], 200);
    }
}
