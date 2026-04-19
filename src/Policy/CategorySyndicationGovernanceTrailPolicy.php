<?php

declare(strict_types=1);

namespace App\Vendoring\Policy;

use App\Vendoring\PolicyInterface\CategorySyndicationGovernanceTrailPolicyInterface;
use App\Vendoring\ValueObject\CategorySyndicationGovernanceTrailReport;

final class CategorySyndicationGovernanceTrailPolicy implements CategorySyndicationGovernanceTrailPolicyInterface
{
    public function buildReport(
        array $policyAwarePayload,
        array $deliveryPayload,
        array $historyPayload,
        array $recoveryPayload,
    ): CategorySyndicationGovernanceTrailReport {
        $warnings = self::stringList($policyAwarePayload['warnings'] ?? []);
        $fallbackUsed = (bool) ($policyAwarePayload['fallbackUsed'] ?? false);
        if ($fallbackUsed) {
            $warnings[] = 'governance_trail_fallback_used';
        }

        $historyCounts = [
            'totalRecords' => self::intValue($historyPayload['totalRecords'] ?? 0),
            'deliveredCount' => self::intValue($historyPayload['deliveredCount'] ?? 0),
            'failedCount' => self::intValue($historyPayload['failedCount'] ?? 0),
            'pendingCount' => self::intValue($historyPayload['pendingCount'] ?? 0),
            'retryScheduledCount' => self::intValue($historyPayload['retryScheduledCount'] ?? 0),
            'skippedCount' => self::intValue($historyPayload['skippedCount'] ?? 0),
        ];

        $retryScheduled = 'retry_scheduled' === ($deliveryPayload['status'] ?? null)
            || self::intValue($recoveryPayload['scheduledRetries'] ?? 0) > 0
            || $historyCounts['retryScheduledCount'] > 0;

        $checks = self::boolMap($policyAwarePayload['checks'] ?? []);
        $checks['governanceTrailHasFailures'] = $historyCounts['failedCount'] > 0;
        $checks['governanceTrailRetryScheduled'] = $retryScheduled;

        return new CategorySyndicationGovernanceTrailReport(
            self::stringValue($policyAwarePayload['destinationId'] ?? $deliveryPayload['destinationId'] ?? ''),
            self::stringValue($policyAwarePayload['categoryId'] ?? $deliveryPayload['categoryId'] ?? ''),
            self::stringValue($policyAwarePayload['mediaPolicyMode'] ?? ''),
            (bool) ($policyAwarePayload['strictPublishable'] ?? false),
            (bool) ($policyAwarePayload['fallbackPublishable'] ?? false),
            (bool) ($policyAwarePayload['resolvedPublishable'] ?? false),
            $fallbackUsed,
            self::stringValue($deliveryPayload['status'] ?? 'unknown'),
            (bool) ($deliveryPayload['retryable'] ?? false),
            $retryScheduled,
            $historyCounts,
            array_values(array_unique($warnings)),
            $checks,
        );
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
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
