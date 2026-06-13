<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface\Vendor;

use App\Vendoring\ValueObject\VendorCategorySyndicationGovernanceTrailReportValueObject;

interface VendorCategorySyndicationGovernanceTrailPolicyInterface
{
    /**
     * @param array<string, mixed> $policyAwarePayload
     * @param array<string, mixed> $deliveryPayload
     * @param array<string, mixed> $historyPayload
     * @param array<string, mixed> $recoveryPayload
     */
    public function buildReport(
        array $policyAwarePayload,
        array $deliveryPayload,
        array $historyPayload,
        array $recoveryPayload,
    ): VendorCategorySyndicationGovernanceTrailReportValueObject;
}
