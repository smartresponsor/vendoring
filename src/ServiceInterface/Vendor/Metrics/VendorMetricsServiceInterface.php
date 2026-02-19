<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Metrics;
interface VendorMetricsServiceInterface
{
    /** @return array{revenue:float, refunds:float, payouts:float, balance:float} */
    public function overview(string $tenantId, string $vendorId, ?string $from = null, ?string $to = null, string $currency = 'USD'): array;

    /** @return array<int,array{period:string,revenue:float,refunds:float,payouts:float,balance:float}> */
    public function trends(string $tenantId, string $vendorId, string $from, string $to, string $bucket = 'month'): array;
}
