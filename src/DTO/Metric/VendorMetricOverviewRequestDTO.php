<?php

declare(strict_types=1);

namespace App\DTO\Metric;

final readonly class VendorMetricOverviewRequestDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public ?string $from = null,
        public ?string $to = null,
        public string $currency = 'USD',
    ) {
    }
}
