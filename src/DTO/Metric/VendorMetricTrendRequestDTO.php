<?php

declare(strict_types=1);

namespace App\DTO\Metric;

final readonly class VendorMetricTrendRequestDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $from,
        public string $to,
        public string $bucket = 'month',
        public string $currency = 'USD',
    ) {}
}
