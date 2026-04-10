<?php

declare(strict_types=1);

namespace App\Policy;

use App\DTO\CatalogSyndication\CategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\PolicyInterface\CategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\ValueObject\CategorySyndicationFallbackAwarePackageGateReport;

final class CategorySyndicationFallbackAwarePackageGatePolicy implements CategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(CategorySyndicationFallbackAwarePackageGateReportInputDTO $input): CategorySyndicationFallbackAwarePackageGateReport
    {
        $strictPublishable = [] === $input->packageMissingRequiredFields && [] === $input->strictMediaRequiredMissing;
        $fallbackPublishable = [] === $input->packageMissingRequiredFields && [] === $input->fallbackMediaRequiredMissing;

        $mergedWarnings = $input->warnings;
        if ($fallbackPublishable && !$strictPublishable) {
            $mergedWarnings[] = 'package_publishable_via_fallback_only';
        }

        $checks = $input->strictChecks;
        foreach ($input->fallbackChecks as $checkName => $value) {
            $checks[$checkName] = $value;
        }
        $checks['strictPackageGatePublishable'] = $strictPublishable;
        $checks['fallbackPackageGatePublishable'] = $fallbackPublishable;

        return new CategorySyndicationFallbackAwarePackageGateReport(
            array_values($input->packageMissingRequiredFields),
            array_values($input->strictMediaRequiredMissing),
            array_values($input->fallbackMediaRequiredMissing),
            array_values(array_unique($mergedWarnings)),
            $checks,
            array_values(array_unique($input->exactMatchedBindingIds)),
            array_values(array_unique($input->fallbackMatchedBindingIds)),
            $strictPublishable,
            $fallbackPublishable,
        );
    }
}
