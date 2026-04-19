<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface;

use App\Vendoring\ValueObject\CategorySyndicationPolicyAwarePackageGateReport;

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
