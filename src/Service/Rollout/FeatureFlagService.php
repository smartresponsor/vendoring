<?php

declare(strict_types=1);

namespace App\Service\Rollout;

use App\ServiceInterface\Rollout\FeatureFlagServiceInterface;
use App\ServiceInterface\Rollout\TrafficCohortResolverInterface;

/**
 * In-memory feature-flag evaluator for controlled rollout decisions.
 *
 * Flags are supplied as configuration arrays keyed by flag name. Each flag may expose:
 * - `enabled` as the global default
 * - `cohorts` as a list of canonical cohort identifiers allowed to receive the flag
 *
 * This service is intentionally read-side only and serves as the current rollout contract
 * for docs, smoke tests, and runtime inspection.
 */
final readonly class FeatureFlagService implements FeatureFlagServiceInterface
{
    /**
     * @param array<string, array{enabled?:bool, cohorts?:list<string>}> $flags
     */
    public function __construct(
        private TrafficCohortResolverInterface $trafficCohortResolver,
        private array $flags = [],
    ) {
    }

    public function isEnabled(string $flagName, ?string $tenantId = null, ?string $vendorId = null): bool
    {
        return $this->explain($flagName, $tenantId, $vendorId)['enabled'];
    }

    public function explain(string $flagName, ?string $tenantId = null, ?string $vendorId = null): array
    {
        $cohort = $this->trafficCohortResolver->resolve($tenantId, $vendorId);
        $flag = $this->flags[$flagName] ?? null;

        if (!is_array($flag)) {
            return [
                'flag' => $flagName,
                'enabled' => false,
                'cohort' => $cohort,
                'reason' => 'flag_not_defined',
            ];
        }

        $defaultEnabled = true === ($flag['enabled'] ?? false);
        $cohorts = $flag['cohorts'] ?? [];

        if ([] === $cohorts) {
            return [
                'flag' => $flagName,
                'enabled' => $defaultEnabled,
                'cohort' => $cohort,
                'reason' => $defaultEnabled ? 'globally_enabled' : 'globally_disabled',
            ];
        }

        $enabled = in_array($cohort, $cohorts, true);

        return [
            'flag' => $flagName,
            'enabled' => $enabled,
            'cohort' => $cohort,
            'reason' => $enabled ? 'cohort_enabled' : 'cohort_disabled',
        ];
    }
}
