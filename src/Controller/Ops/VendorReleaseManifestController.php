<?php

declare(strict_types=1);

namespace App\Controller\Ops;

use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
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
        private readonly ReleaseManifestBuilderInterface $releaseManifestBuilder,
        private readonly RollbackDecisionEvaluatorInterface $rollbackDecisionEvaluator,
    ) {
    }

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
