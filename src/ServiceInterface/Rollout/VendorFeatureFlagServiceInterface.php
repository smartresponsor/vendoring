<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Rollout;

/**
 * Read-side contract for evaluating controlled-rollout feature flags.
 *
 * Implementations expose deterministic enablement decisions for a given flag and runtime
 * cohort without mutating persistent state or external transports.
 */
interface VendorFeatureFlagServiceInterface
{
    /**
     * Determine whether a named feature flag is enabled for the supplied Vendor scope.
     *
     * @param string      $flagName canonical feature flag identifier
     * @param string|null $vendorId optional canonical Vendor scope used for cohort-aware rollout
     *
     * @return bool true when the flag is enabled for the resolved cohort
     */
    public function isEnabled(string $flagName, ?string $vendorId = null): bool;

    /**
     * Explain the rollout decision for a named feature flag.
     *
     * @param string      $flagName canonical feature flag identifier
     * @param string|null $vendorId optional canonical Vendor scope used for cohort-aware rollout
     *
     * @return array{flag:string, enabled:bool, cohort:string, reason:string} stable decision payload
     *                                                                        suitable for docs, smoke, and runtime inspection
     */
    public function explain(string $flagName, ?string $vendorId = null): array;
}
