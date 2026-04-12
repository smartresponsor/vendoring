<?php

declare(strict_types=1);

namespace App\Service\Rollout;

use App\ServiceInterface\Rollout\TrafficCohortResolverInterface;

/**
 * Deterministic resolver for rollout cohorts based on tenant/vendor identity.
 *
 * The resolver keeps cohort routing intentionally simple for the modular-monolith stage:
 * vendor scope is preferred over tenant scope, and missing scope falls back to `global`.
 */
final class TrafficCohortResolver implements TrafficCohortResolverInterface
{
    public function resolve(?string $tenantId = null, ?string $vendorId = null): string
    {
        if (null !== $vendorId && '' !== trim($vendorId)) {
            return 'vendor:' . trim($vendorId);
        }

        if (null !== $tenantId && '' !== trim($tenantId)) {
            return 'tenant:' . trim($tenantId);
        }

        return 'global';
    }
}
