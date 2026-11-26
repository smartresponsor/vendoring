<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Payout;

final class VendorCreatePayoutDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $currency,
        public int $thresholdCents,
        public float $retentionFeePercent, // e.g. 0.05 for 5%
    ) {
        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
        if ('' === trim($vendorId) || '' === trim($currency) || $thresholdCents <= 0 || !is_finite($retentionFeePercent) || $retentionFeePercent < 0 || $retentionFeePercent > 1) {
            throw new \InvalidArgumentException('Invalid payout scope or financial parameters');
        }
    }
}
