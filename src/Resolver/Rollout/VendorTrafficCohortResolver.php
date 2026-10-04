<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Rollout;

use App\Vendoring\ResolverInterface\Rollout\VendorTrafficCohortResolverInterface;

/**
 * Deterministic resolver for rollout cohorts based on canonical Vendor identity.
 *
 * Missing Vendor scope falls back to `global`.
 */
final class VendorTrafficCohortResolver implements VendorTrafficCohortResolverInterface
{
    public function resolve(?string $vendorId = null): string
    {
        return null !== $vendorId && '' !== trim($vendorId)
            ? 'vendor:'.trim($vendorId)
            : 'global';
    }
}
