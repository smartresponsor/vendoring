<?php

declare(strict_types=1);

namespace App\Vendoring\Policy\Vendor;

use App\Vendoring\DTO\CatalogSyndication\VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\Vendoring\PolicyInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\Vendoring\ValueObject\VendorCategorySyndicationFallbackAwarePackageGateReportValueObject;

final class VendorCategorySyndicationFallbackAwarePackageGatePolicy implements VendorCategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO $input): VendorCategorySyndicationFallbackAwarePackageGateReportValueObject
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

        return new VendorCategorySyndicationFallbackAwarePackageGateReportValueObject(
            ($input->packageMissingRequiredFields),
            ($input->strictMediaRequiredMissing),
            ($input->fallbackMediaRequiredMissing),
            self::stringList(array_unique($mergedWarnings)),
            $checks,
            self::stringList(array_unique($input->exactMatchedBindingIds)),
            self::stringList(array_unique($input->fallbackMatchedBindingIds)),
            $strictPublishable,
            $fallbackPublishable,
        );
    }

    /**
     * @param array<int, mixed> $value
     * @return list<string>
     */
    private static function stringList(array $value): array
    {
        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }
}
