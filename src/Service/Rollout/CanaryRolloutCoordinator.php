<?php

declare(strict_types=1);

namespace App\Service\Rollout;

use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
use App\ServiceInterface\Rollout\CanaryRolloutCoordinatorInterface;
use App\ServiceInterface\Rollout\FeatureFlagServiceInterface;
use App\ServiceInterface\Rollout\TrafficCohortResolverInterface;

/**
 * Read-side coordinator that turns rollout flags, cohorts, probes, and rollback signals into one
 * operator-friendly canary verdict.
 */
final class CanaryRolloutCoordinator implements CanaryRolloutCoordinatorInterface
{
    public function __construct(
        private readonly FeatureFlagServiceInterface $featureFlagService,
        private readonly TrafficCohortResolverInterface $trafficCohortResolver,
        private readonly ReleaseManifestBuilderInterface $releaseManifestBuilder,
        private readonly RollbackDecisionEvaluatorInterface $rollbackDecisionEvaluator,
    ) {
    }

    public function evaluate(string $flagName, ?string $tenantId = null, ?string $vendorId = null, int $windowSeconds = 900): array
    {
        $windowSeconds = max(1, $windowSeconds);
        $flagDecision = $this->featureFlagService->explain($flagName, $tenantId, $vendorId);
        $cohort = $this->trafficCohortResolver->resolve($tenantId, $vendorId);
        $manifest = $this->releaseManifestBuilder->build($windowSeconds);
        $rollback = $this->rollbackDecisionEvaluator->evaluate($manifest);
        $probeGate = $this->probeGate($manifest);

        [$decision, $recommendedAction, $reason] = $this->rolloutDecision($flagDecision, $rollback, $probeGate);

        return [
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'flagDecision' => $flagDecision,
            'manifest' => $manifest,
            'rollback' => $rollback,
            'canary' => [
                'cohort' => $cohort,
                'decision' => $decision,
                'recommendedAction' => $recommendedAction,
                'nextCohort' => $this->nextCohort($cohort, $tenantId),
                'reason' => $reason,
                'probeGate' => $probeGate,
            ],
        ];
    }

    /**
     * @param array{flag:string, enabled:bool, cohort:string, reason:string} $flagDecision
     * @param array<string,mixed> $rollback
     * @param array{transaction:bool, finance:bool, payout:bool, postDeploy:bool} $probeGate
     *
     * @return array{string,string,string}
     */
    private function rolloutDecision(array $flagDecision, array $rollback, array $probeGate): array
    {
        if (false === $flagDecision['enabled']) {
            return ['disabled', 'keep_flag_disabled', (string) $flagDecision['reason']];
        }

        if (in_array(false, $probeGate, true)) {
            return ['hold', 'keep_current_canary_scope', 'required_probe_missing'];
        }

        $rollbackDecision = (string) ($rollback['decision'] ?? 'hold');
        if ('rollback' === $rollbackDecision) {
            return ['rollback', 'disable_flag_for_current_cohort', 'rollback_decision_triggered'];
        }

        if ('hold' === $rollbackDecision) {
            return ['hold', 'keep_current_canary_scope', 'release_manifest_hold'];
        }

        if ('global' === $flagDecision['cohort']) {
            return ['stable', 'keep_global_rollout', 'global_canary_green'];
        }

        return ['proceed', 'expand_canary_scope', 'current_canary_green'];
    }

    /**
     * @param array<string,mixed> $manifest
     *
     * @return array{transaction:bool, finance:bool, payout:bool, postDeploy:bool}
     */
    private function probeGate(array $manifest): array
    {
        $missing = $manifest['monitoring']['missingProbes'] ?? [];
        if (!is_array($missing)) {
            $missing = [];
        }

        return [
            'transaction' => !in_array('transaction', $missing, true),
            'finance' => !in_array('finance', $missing, true),
            'payout' => !in_array('payout', $missing, true),
            'postDeploy' => !in_array('postDeploy', $missing, true),
        ];
    }

    private function nextCohort(string $currentCohort, ?string $tenantId): ?string
    {
        if (str_starts_with($currentCohort, 'vendor:')) {
            return null !== $tenantId && '' !== trim($tenantId) ? 'tenant:'.trim($tenantId) : 'global';
        }

        if (str_starts_with($currentCohort, 'tenant:')) {
            return 'global';
        }

        return null;
    }
}
