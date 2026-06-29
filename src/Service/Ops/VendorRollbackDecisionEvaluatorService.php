<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ops;

use App\Vendoring\ServiceInterface\Ops\VendorRollbackDecisionEvaluatorServiceInterface;

/**
 * Deterministic rollback evaluator for release operators.
 *
 * Turns monitoring signals and manifest completeness into one operational decision:
 * proceed, hold, or rollback.
 *
 * Reason deduplication: outbound_circuit_open is already represented by the
 * critical alert code path, so the open_breakers_present reason is suppressed
 * when outbound_circuit_open is already a recorded reason to avoid confusing
 * duplicate entries in operator-facing output.
 */
final readonly class VendorRollbackDecisionEvaluatorService implements VendorRollbackDecisionEvaluatorServiceInterface
{
    /**
     * @param array{criticalAlertCodes?:list<string>,warningAlertCodes?:list<string>} $thresholds
     */
    public function __construct(private array $thresholds = [])
    {
    }

    public function evaluate(array $manifest): array
    {
        $criticalAlertCodes = $this->thresholds['criticalAlertCodes'] ?? ['outbound_circuit_open'];
        $warningAlertCodes = $this->thresholds['warningAlertCodes'] ?? [
            'runtime_error_spike',
            'probe_artifacts_missing',
            'observability_metrics_empty',
        ];

        $reasons = [];
        $decision = 'proceed';
        $severity = 'info';

        $missingDocs = array_keys(array_filter($manifest['releaseDocs'], static fn (bool $p): bool => !$p));
        $missingArtifacts = array_keys(array_filter($manifest['buildArtifacts'], static fn (bool $p): bool => !$p));
        $alertCodes = $manifest['monitoring']['alertCodes'];

        // --- critical path --------------------------------------------------
        foreach ($alertCodes as $code) {
            if (in_array($code, $criticalAlertCodes, true)) {
                $decision = 'rollback';
                $severity = 'critical';
                $this->addReason($reasons, 'critical_alert:'.$code);
            }
        }

        // open_breakers_present is only added when not already covered by a
        // critical alert code reason to avoid duplicate operator-facing output.
        if ($manifest['monitoring']['openBreakers'] > 0) {
            $decision = 'rollback';
            $severity = 'critical';

            $alreadyCoveredByCriticalAlert = array_any(
                $reasons,
                static fn (string $r): bool => str_starts_with($r, 'critical_alert:'),
            );

            if (!$alreadyCoveredByCriticalAlert) {
                $this->addReason($reasons, 'open_breakers_present');
            }
        }

        // --- warning path (skipped when already rolling back) ---------------
        if ('rollback' !== $decision) {
            foreach ($alertCodes as $code) {
                if (in_array($code, $warningAlertCodes, true)) {
                    $decision = 'hold';
                    $severity = 'warning';
                    $this->addReason($reasons, 'warning_alert:'.$code);
                }
            }

            if ([] !== $missingDocs) {
                $decision = 'hold';
                $severity = 'warning';
                $this->addReason($reasons, 'missing_release_docs:'.implode(',', $missingDocs));
            }

            if ([] !== $missingArtifacts) {
                $decision = 'hold';
                $severity = 'warning';
                $this->addReason($reasons, 'missing_build_artifacts:'.implode(',', $missingArtifacts));
            }

            if ([] !== $manifest['monitoring']['missingProbes']) {
                $decision = 'hold';
                $severity = 'warning';
                $this->addReason($reasons, 'missing_probes:'.implode(',', $manifest['monitoring']['missingProbes']));
            }
        }

        if ([] === $reasons) {
            $reasons[] = 'release_manifest_green';
        }

        return [
            'generatedAt' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            'decision' => $decision,
            'severity' => $severity,
            'reasons' => array_values($reasons),
            'actions' => $this->actionsFor($decision),
        ];
    }

    /** @param list<string> $reasons */
    private function addReason(array &$reasons, string $reason): void
    {
        if (!in_array($reason, $reasons, true)) {
            $reasons[] = $reason;
        }
    }

    /** @return list<string> */
    private function actionsFor(string $decision): array
    {
        return match ($decision) {
            'rollback' => [
                'freeze_new_rollout',
                'revert_runtime_traffic',
                'review_breaker_and_error_alerts',
                'execute_schema_safe_rollback_checks',
            ],
            'hold' => [
                'pause_promotion',
                'repair_missing_artifacts_or_probes',
                'rerun_post_deploy_verification',
            ],
            default => [
                'continue_release_candidate_validation',
            ],
        };
    }
}
