<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ServiceInterface\Ops\VendorReleaseManifestBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ops\VendorRollbackDecisionEvaluatorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-side operator endpoint for the current release manifest and rollback recommendation.
 */
#[Route('/api/vendor-monitoring')]
final class VendorReleaseManifestController extends AbstractController
{
    public function __construct(
        private readonly VendorReleaseManifestBuilderServiceInterface $releaseManifestBuilder,
        private readonly VendorRollbackDecisionEvaluatorServiceInterface $rollbackDecisionEvaluator,
    ) {}

    /**
     * Render the current release manifest and rollback decision.
     */
    #[Route('/release-manifest', methods: ['GET'])]
    public function overview(Request $request): JsonResponse
    {
        $windowSeconds = max(1, (int) $request->query->get('windowSeconds', 900));
        $manifest = $this->releaseManifestBuilder->build($windowSeconds);
        $rollback = $this->rollbackDecisionEvaluator->evaluate($manifest);

        return new JsonResponse([
            'data' => [
                'manifest' => $manifest,
                'rollback' => $rollback,
            ],
        ], 200);
    }
}
