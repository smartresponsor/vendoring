<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Metric;

use Doctrine\DBAL\Exception;
use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Metric\VendorMetricTrendRequestDTO;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/metrics/vendor')]
final class VendorMetricController extends AbstractController
{
    public function __construct(private readonly VendorMetricServiceInterface $svc) {}

    #[Route('/{vendorId}/overview', methods: ['GET'])]
    /** @throws Exception */
    public function overview(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string) ($r->query->get('tenantId') ?? '');
        $from = $r->query->get('from');
        $to = $r->query->get('to');
        $currency = (string) ($r->query->get('currency') ?? 'USD');
        if (!$tenantId) {
            return new JsonResponse(['error' => 'tenantId required'], 422);
        }
        $data = $this->svc->overview(new VendorMetricOverviewRequestDTO(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from ? (string) $from : null,
            to: $to ? (string) $to : null,
            currency: $currency,
        ));

        return new JsonResponse(['data' => $data], 200);
    }

    #[Route('/{vendorId}/trends', methods: ['GET'])]
    /** @throws Exception */
    public function trends(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string) ($r->query->get('tenantId') ?? '');
        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $bucket = (string) ($r->query->get('bucket') ?? 'month');
        $currency = (string) ($r->query->get('currency') ?? 'USD');
        if (!$tenantId || !$from || !$to) {
            return new JsonResponse(['error' => 'tenantId, from, to required'], 422);
        }
        $data = $this->svc->trends(new VendorMetricTrendRequestDTO(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from,
            to: $to,
            bucket: $bucket,
            currency: $currency,
        ));

        return new JsonResponse(['data' => $data], 200);
    }
}
