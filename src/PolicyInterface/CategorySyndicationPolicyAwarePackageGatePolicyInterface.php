<?php

declare(strict_types=1);

namespace App\PolicyInterface;

use App\ValueObject\CategorySyndicationPolicyAwarePackageGateReport;

interface CategorySyndicationPolicyAwarePackageGatePolicyInterface
{
    /**
     * @param list<string>         $packageMissingRequiredFields
     * @param array<string, mixed> $policyPayload
     * @param array<string, mixed> $fallbackGatePayload
     */
    public function buildReport(
        array $packageMissingRequiredFields,
        array $policyPayload,
        array $fallbackGatePayload,
    ): CategorySyndicationPolicyAwarePackageGateReport;
}
