<?php

declare(strict_types=1);

namespace App\PolicyInterface;

use App\ValueObject\CategorySyndicationFallbackAwarePackageGateReport;

interface CategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    /**
     * @param list<string>        $packageMissingRequiredFields
     * @param list<string>        $strictMediaRequiredMissing
     * @param list<string>        $fallbackMediaRequiredMissing
     * @param list<string>        $warnings
     * @param array<string, bool> $strictChecks
     * @param array<string, bool> $fallbackChecks
     * @param list<string>        $exactMatchedBindingIds
     * @param list<string>        $fallbackMatchedBindingIds
     */
    public function buildReport(
        array $packageMissingRequiredFields,
        array $strictMediaRequiredMissing,
        array $fallbackMediaRequiredMissing,
        array $warnings,
        array $strictChecks,
        array $fallbackChecks,
        array $exactMatchedBindingIds,
        array $fallbackMatchedBindingIds,
    ): CategorySyndicationFallbackAwarePackageGateReport;
}
