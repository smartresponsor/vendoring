<?php

declare(strict_types=1);

namespace App\Policy;

use App\PolicyInterface\CategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\ValueObject\CategorySyndicationFallbackAwarePackageGateReport;

final class CategorySyndicationFallbackAwarePackageGatePolicy implements CategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(
        array $packageMissingRequiredFields,
        array $strictMediaRequiredMissing,
        array $fallbackMediaRequiredMissing,
        array $warnings,
        array $strictChecks,
        array $fallbackChecks,
        array $exactMatchedBindingIds,
        array $fallbackMatchedBindingIds,
    ): CategorySyndicationFallbackAwarePackageGateReport {
        $strictPublishable = [] === $packageMissingRequiredFields && [] === $strictMediaRequiredMissing;
        $fallbackPublishable = [] === $packageMissingRequiredFields && [] === $fallbackMediaRequiredMissing;

        $mergedWarnings = $warnings;
        if ($fallbackPublishable && !$strictPublishable) {
            $mergedWarnings[] = 'package_publishable_via_fallback_only';
        }

        $checks = $strictChecks;
        foreach ($fallbackChecks as $checkName => $value) {
            $checks[$checkName] = $value;
        }
        $checks['strictPackageGatePublishable'] = $strictPublishable;
        $checks['fallbackPackageGatePublishable'] = $fallbackPublishable;

        return new CategorySyndicationFallbackAwarePackageGateReport(
            array_values($packageMissingRequiredFields),
            array_values($strictMediaRequiredMissing),
            array_values($fallbackMediaRequiredMissing),
            array_values(array_unique($mergedWarnings)),
            $checks,
            array_values(array_unique($exactMatchedBindingIds)),
            array_values(array_unique($fallbackMatchedBindingIds)),
            $strictPublishable,
            $fallbackPublishable,
        );
    }
}
