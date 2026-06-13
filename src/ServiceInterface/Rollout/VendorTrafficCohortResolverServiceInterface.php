<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Rollout;

/**
 * Read-side contract for resolving rollout cohorts from tenant/vendor identity.
 *
 * Implementations must remain deterministic for the same input so that feature-flag
 * routing, canary rollout, and synthetic verification can reason about the same cohort.
 */
interface VendorTrafficCohortResolverServiceInterface
{
    /**
     * Resolve the canonical rollout cohort for the provided runtime scope.
     *
     * @param string|null $tenantId Canonical tenant scope when the rollout is tenant-aware.
     * @param string|null $vendorId Canonical vendor scope when the rollout is vendor-aware.
     *
     * @return string Stable cohort identifier such as `global`, `tenant:<id>`, or `vendor:<id>`.
     */
    public function resolve(?string $tenantId = null, ?string $vendorId = null): string;
}
