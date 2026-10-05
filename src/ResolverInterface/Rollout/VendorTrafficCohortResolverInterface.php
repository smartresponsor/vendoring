<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Rollout;

/**
 * Read-side contract for resolving rollout cohorts from canonical Vendor identity.
 *
 * Implementations must remain deterministic for the same input so that feature-flag
 * routing, canary rollout, and synthetic verification can reason about the same cohort.
 */
interface VendorTrafficCohortResolverInterface
{
    /**
     * Resolve the canonical rollout cohort for the provided runtime scope.
     *
     * @param string|null $vendorId canonical Vendor scope
     *
     * @return string stable cohort identifier: `global` or `vendor:<id>`
     */
    public function resolve(?string $vendorId = null): string;
}
