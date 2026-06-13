<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Monitoring;

use App\Vendoring\ServiceInterface\Observability\VendorAlertRuleEvaluatorServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorMonitoringSnapshotBuilderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorMonitoringOverviewService
{
    public function __construct(
        private VendorMonitoringSnapshotBuilderServiceInterface $snapshotBuilder,
        private VendorAlertRuleEvaluatorServiceInterface $alertRuleEvaluator,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

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
