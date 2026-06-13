<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Release\Manifest;

use App\Vendoring\ServiceInterface\Ops\VendorReleaseManifestBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ops\VendorRollbackDecisionEvaluatorServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorReleaseManifestOverviewService
{
    public function __construct(
        private VendorReleaseManifestBuilderServiceInterface $releaseManifestBuilder,
        private VendorRollbackDecisionEvaluatorServiceInterface $rollbackDecisionEvaluator,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

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
