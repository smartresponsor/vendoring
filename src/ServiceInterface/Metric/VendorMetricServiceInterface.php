<?php

declare(strict_types=1);

namespace App\ServiceInterface\Metric;

use App\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\DTO\Metric\VendorMetricTrendRequestDTO;

interface VendorMetricServiceInterface
{
    /** @return array{tenantId:string, vendorId:string, from:?string, to:?string, currency:string, revenue:float, refunds:float, payouts:float, balance:float} */
    public function overview(VendorMetricOverviewRequestDTO $request): array;

    /** @return array<int,array{tenantId:string, vendorId:string, from:string, to:string, currency:string, bucket:string, period:string, revenue:float, refunds:float, payouts:float, balance:float}> */
    public function trends(VendorMetricTrendRequestDTO $request): array;
}
