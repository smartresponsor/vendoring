<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Payout;

final class CreatePayoutDTO
{
    public function __construct(
        public string $vendorId,
        public string $currency,
        public int $thresholdCents,
        public float $retentionFeePercent, // e.g. 0.05 for 5%
        public string $tenantId = '',
    ) {}
}
