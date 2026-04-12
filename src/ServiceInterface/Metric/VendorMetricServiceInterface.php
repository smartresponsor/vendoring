<?php

declare(strict_types=1);

namespace App\ServiceInterface\Metric;

use App\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\DTO\Metric\VendorMetricTrendRequestDTO;
use Doctrine\DBAL\Exception;

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
     * @throws Exception
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
     * @throws Exception
     */
    public function trends(VendorMetricTrendRequestDTO $request): array;
}
