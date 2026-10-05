<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Metric;

use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Metric\VendorMetricTrendRequestDTO;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorMetricHttpService
{
    public function __construct(private readonly VendorMetricServiceInterface $svc)
    {
    }

    public function overview(string $vendorId, Request $r): JsonResponse
    {
        $from = $r->query->get('from');
        $to = $r->query->get('to');
        $currency = (string) ($r->query->get('currency') ?? 'USD');
        $data = $this->svc->overview(new VendorMetricOverviewRequestDTO(
            vendorId: $vendorId,
            from: $from ? (string) $from : null,
            to: $to ? (string) $to : null,
            currency: $currency,
        ));

        return new JsonResponse(['data' => $data], 200);
    }

    public function trends(string $vendorId, Request $r): JsonResponse
    {
        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $bucket = (string) ($r->query->get('bucket') ?? 'month');
        $currency = (string) ($r->query->get('currency') ?? 'USD');
        if (!$from || !$to) {
            return new JsonResponse(['error' => 'from, to required'], 422);
        }
        $data = $this->svc->trends(new VendorMetricTrendRequestDTO(
            vendorId: $vendorId,
            from: $from,
            to: $to,
            bucket: $bucket,
            currency: $currency,
        ));

        return new JsonResponse(['data' => $data], 200);
    }
}
