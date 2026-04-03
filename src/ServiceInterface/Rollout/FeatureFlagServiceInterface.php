<?php

declare(strict_types=1);

namespace App\ServiceInterface\Rollout;

/**
 * Read-side contract for evaluating controlled-rollout feature flags.
 *
 * Implementations expose deterministic enablement decisions for a given flag and runtime
 * cohort without mutating persistent state or external transports.
 */
interface FeatureFlagServiceInterface
{
    /**
     * Determine whether a named feature flag is enabled for the supplied tenant/vendor scope.
     *
     * @param string      $flagName Canonical feature flag identifier.
     * @param string|null $tenantId Optional tenant scope used for cohort-aware rollout.
     * @param string|null $vendorId Optional vendor scope used for cohort-aware rollout.
     *
     * @return bool True when the flag is enabled for the resolved cohort.
     */
    public function isEnabled(string $flagName, ?string $tenantId = null, ?string $vendorId = null): bool;

    /**
     * Explain the rollout decision for a named feature flag.
     *
     * @param string      $flagName Canonical feature flag identifier.
     * @param string|null $tenantId Optional tenant scope used for cohort-aware rollout.
     * @param string|null $vendorId Optional vendor scope used for cohort-aware rollout.
     *
     * @return array{flag:string, enabled:bool, cohort:string, reason:string} Stable decision payload
     *                                                                     suitable for docs, smoke, and runtime inspection.
     */
    public function explain(string $flagName, ?string $tenantId = null, ?string $vendorId = null): array;
}
