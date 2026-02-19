<?php
declare(strict_types = 1);

namespace App\Controller\Vendor\Metrics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Vendor\Metrics\VendorMetricsService;

#[Route('/api/metrics/vendor')]
final class VendorMetricsController extends AbstractController
{
    public function __construct(private readonly VendorMetricsService $svc)
    {
    }

    #[Route('/{vendorId}/overview', methods: ['GET'])]
    public function overview(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string)($r->query->get('tenantId') ?? '');
        $from = $r->query->get('from');
        $to = $r->query->get('to');
        $currency = (string)($r->query->get('currency') ?? 'USD');
        if (!$tenantId) return new JsonResponse(['error' => 'tenantId required'], 422);
        $data = $this->svc->overview($tenantId, $vendorId, $from ? (string)$from : null, $to ? (string)$to : null, $currency);
        return new JsonResponse(['data' => $data], 200);
    }

    #[Route('/{vendorId}/trends', methods: ['GET'])]
    public function trends(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string)($r->query->get('tenantId') ?? '');
        $from = (string)($r->query->get('from') ?? '');
        $to = (string)($r->query->get('to') ?? '');
        $bucket = (string)($r->query->get('bucket') ?? 'month');
        if (!$tenantId || !$from || !$to) return new JsonResponse(['error' => 'tenantId, from, to required'], 422);
        $data = $this->svc->trends($tenantId, $vendorId, $from, $to, $bucket);
        return new JsonResponse(['data' => $data], 200);
    }
}
