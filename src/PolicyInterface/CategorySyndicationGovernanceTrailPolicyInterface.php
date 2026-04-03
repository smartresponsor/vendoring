<?php

declare(strict_types=1);

namespace App\PolicyInterface;

use App\ValueObject\CategorySyndicationGovernanceTrailReport;

interface CategorySyndicationGovernanceTrailPolicyInterface
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
    ): CategorySyndicationGovernanceTrailReport;
}
