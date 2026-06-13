<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Metric;

use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Metric\VendorMetricTrendRequestDTO;

interface VendorMetricServiceInterface
{
    /**
     * @return array{
     *   'tenantId': string,
     *   'vendorId': string,
     *   'from': ?string,
     *   'to': ?string,
     *   'currency': string,
     *   'revenue': float,
     *   'refunds': float,
     *   'payouts': float,
     *   'balance': float
     * }
     */
    public function overview(VendorMetricOverviewRequestDTO $request): array;

    /**
     * @return list<array{
     *   'tenantId': string,
     *   'vendorId': string,
     *   'from': string,
     *   'to': string,
     *   'currency': string,
     *   'bucket': string,
     *   'period': string,
     *   'revenue': float,
     *   'refunds': float,
     *   'payouts': float,
     *   'balance': float
     * }>
     */
    public function trends(VendorMetricTrendRequestDTO $request): array;
}
