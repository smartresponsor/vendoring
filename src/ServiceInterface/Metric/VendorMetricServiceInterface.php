<?php

declare(strict_types=1);

namespace App\ServiceInterface\Metric;

interface VendorMetricServiceInterface
{
    /**
     * @return array{
     *     tenantId:string,
     *     vendorId:string,
     *     from:?string,
     *     to:?string,
     *     currency:string,
     *     revenue:float,
     *     refunds:float,
     *     payouts:float,
     *     balance:float
     * }
     */
    public function overview(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): array;

    /**
     * @return array<int, array{
     *     tenantId:string,
     *     vendorId:string,
     *     from:string,
     *     to:string,
     *     currency:string,
     *     bucket:string,
     *     period:string,
     *     revenue:float,
     *     refunds:float,
     *     payouts:float,
     *     balance:float
     * }>
     */
    public function trends(
        string $tenantId,
        string $vendorId,
        string $from,
        string $to,
        string $bucket = 'month',
        string $currency = 'USD',
    ): array;
}
