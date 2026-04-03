<?php

declare(strict_types=1);

namespace App\ServiceInterface\Rollout;

/**
 * Read-side contract for wiring feature flags, cohorts, runtime probes, and rollback decisions
 * into one canary rollout verdict.
 */
interface CanaryRolloutCoordinatorInterface
{
    /**
     * Evaluate canary rollout readiness for one feature flag and runtime cohort.
     *
     * @param string      $flagName      Canonical feature flag identifier.
     * @param string|null $tenantId      Optional tenant scope used for cohort routing.
     * @param string|null $vendorId      Optional vendor scope used for cohort routing.
     * @param int         $windowSeconds Monitoring and rollback evaluation lookback window.
     *
     * @return array{
     *   generatedAt:string,
     *   flagDecision:array{flag:string, enabled:bool, cohort:string, reason:string},
     *   manifest:array<string,mixed>,
     *   rollback:array<string,mixed>,
     *   canary:array{
     *     cohort:string,
     *     decision:string,
     *     recommendedAction:string,
     *     nextCohort:?string,
     *     reason:string,
     *     probeGate:array{transaction:bool, finance:bool, payout:bool, postDeploy:bool}
     *   }
     * }
     */
    public function evaluate(string $flagName, ?string $tenantId = null, ?string $vendorId = null, int $windowSeconds = 900): array;
}
