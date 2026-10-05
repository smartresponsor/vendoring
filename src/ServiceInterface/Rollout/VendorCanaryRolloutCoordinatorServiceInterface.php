<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Rollout;

/**
 * Read-side contract for wiring feature flags, cohorts, runtime probes, and rollback decisions
 * into one canary rollout verdict.
 */
interface VendorCanaryRolloutCoordinatorServiceInterface
{
    /**
     * Evaluate canary rollout readiness for one feature flag and runtime cohort.
     *
     * @param string      $flagName      canonical feature flag identifier
     * @param string|null $vendorId      optional canonical Vendor scope used for cohort routing
     * @param int         $windowSeconds monitoring and rollback evaluation lookback window
     *
     * @return array{
     *   'generatedAt': string,
     *   'flagDecision': array{'flag': string, 'enabled': bool, 'cohort': string, 'reason': string},
     *   'manifest': array<string, mixed>,
     *   'rollback': array<string, mixed>,
     *   'canary': array{
     *     'cohort': string,
     *     'decision': string,
     *     'recommendedAction': string,
     *     'nextCohort': ?string,
     *     'reason': string,
     *     'probeGate': array{
     *       'transaction': bool,
     *       'finance': bool,
     *       'payout': bool,
     *       'postDeploy': bool
     *     }
     *   }
     * }
     */
    public function evaluate(string $flagName, ?string $vendorId = null, int $windowSeconds = 900): array;
}
