<?php

declare(strict_types=1);

namespace App\Policy;

use App\PolicyInterface\CategorySyndicationPolicyAwarePackageGatePolicyInterface;
use App\ValueObject\CategorySyndicationPolicyAwarePackageGateReport;

final class CategorySyndicationPolicyAwarePackageGatePolicy implements CategorySyndicationPolicyAwarePackageGatePolicyInterface
{
    private const string DEFAULT_MEDIA_POLICY_MODE = 'prefer_exact';

    public function buildReport(
        array $packageMissingRequiredFields,
        array $policyPayload,
        array $fallbackGatePayload,
    ): CategorySyndicationPolicyAwarePackageGateReport {
        $mediaPolicyMode = self::mediaPolicyModeOrDefault($policyPayload['mediaPolicyMode'] ?? null);
        $strictPublishable = (bool) ($policyPayload['strictPublishable'] ?? $fallbackGatePayload['strictPublishable'] ?? false);
        $fallbackPublishable = (bool) ($policyPayload['fallbackPublishable'] ?? $fallbackGatePayload['fallbackPublishable'] ?? false);
        $resolvedPublishable = (bool) ($policyPayload['resolvedPublishable'] ?? $fallbackPublishable);
        $fallbackUsed = (bool) ($policyPayload['fallbackUsed'] ?? (!$strictPublishable && $resolvedPublishable));
        $requiredMissing = self::stringList($policyPayload['requiredMissing'] ?? []);
        $warnings = array_merge(
            self::stringList($fallbackGatePayload['warnings'] ?? []),
            self::stringList($policyPayload['warnings'] ?? []),
        );

        if ($resolvedPublishable && $fallbackUsed) {
            $warnings[] = 'package_publishable_by_destination_media_policy_fallback';
        }

        $checks = self::boolMap($fallbackGatePayload['checks'] ?? []);
        foreach (self::boolMap($policyPayload['checks'] ?? []) as $name => $value) {
            $checks[$name] = $value;
        }
        $checks['resolvedPublishable'] = $resolvedPublishable;

        return new CategorySyndicationPolicyAwarePackageGateReport(
            $mediaPolicyMode,
            array_values($packageMissingRequiredFields),
            $requiredMissing,
            array_values(array_unique($warnings)),
            $checks,
            self::stringList($fallbackGatePayload['exactMatchedBindingIds'] ?? []),
            self::stringList($fallbackGatePayload['fallbackMatchedBindingIds'] ?? []),
            $strictPublishable,
            $fallbackPublishable,
            $resolvedPublishable,
            $fallbackUsed,
        );
    }

    private static function mediaPolicyModeOrDefault(mixed $value): string
    {
        return is_scalar($value) && '' !== trim((string) $value) ? trim((string) $value) : self::DEFAULT_MEDIA_POLICY_MODE;
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }

    /**
     * @return array<string, bool>
     */
    private static function boolMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = (bool) $item;
            }
        }

        return $result;
    }
}
