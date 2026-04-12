<?php

declare(strict_types=1);

namespace App\Service\Ops;

use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
use DateTimeImmutable;

/**
 * Deterministic rollback evaluator for release operators.
 *
 * The evaluator turns monitoring signals and manifest completeness into one operational decision:
 * proceed, hold, or rollback.
 */
final readonly class RollbackDecisionEvaluator implements RollbackDecisionEvaluatorInterface
{
    /**
     * @param array{criticalAlertCodes?:list<string>,warningAlertCodes?:list<string>} $thresholds
     */
    public function __construct(private array $thresholds = []) {}

    public function evaluate(array $manifest): array
    {
        $criticalAlertCodes = $this->thresholds['criticalAlertCodes'] ?? ['outbound_circuit_open'];
        $warningAlertCodes = $this->thresholds['warningAlertCodes'] ?? ['runtime_error_spike', 'probe_artifacts_missing', 'observability_metrics_empty'];

        $reasons = [];
        $decision = 'proceed';
        $severity = 'info';

        $missingDocs = array_keys(array_filter($manifest['releaseDocs'], static fn(bool $present): bool => false === $present));
        $missingArtifacts = array_keys(array_filter($manifest['buildArtifacts'], static fn(bool $present): bool => false === $present));
        $alertCodes = $manifest['monitoring']['alertCodes'];

        foreach ($alertCodes as $code) {
            if (in_array($code, $criticalAlertCodes, true)) {
                $decision = 'rollback';
                $severity = 'critical';
                $reasons[] = 'critical_alert:' . $code;
            }
        }

        if ($manifest['monitoring']['openBreakers'] > 0) {
            $decision = 'rollback';
            $severity = 'critical';
            $reasons[] = 'open_breakers_present';
        }

        if ('rollback' !== $decision) {
            foreach ($alertCodes as $code) {
                if (in_array($code, $warningAlertCodes, true)) {
                    $decision = 'hold';
                    $severity = 'warning';
                    $reasons[] = 'warning_alert:' . $code;
                }
            }

            if ([] !== $missingDocs) {
                $decision = 'hold';
                $severity = 'warning';
                $reasons[] = 'missing_release_docs:' . implode(',', $missingDocs);
            }

            if ([] !== $missingArtifacts) {
                $decision = 'hold';
                $severity = 'warning';
                $reasons[] = 'missing_build_artifacts:' . implode(',', $missingArtifacts);
            }

            if ([] !== $manifest['monitoring']['missingProbes']) {
                $decision = 'hold';
                $severity = 'warning';
                $reasons[] = 'missing_probes:' . implode(',', $manifest['monitoring']['missingProbes']);
            }
        }

        if ([] === $reasons) {
            $reasons[] = 'release_manifest_green';
        }

        $generatedAt = new DateTimeImmutable();

        return [
            'generatedAt' => $generatedAt->format(DATE_ATOM),
            'decision' => $decision,
            'severity' => $severity,
            'reasons' => $reasons,
            'actions' => $this->actionsFor($decision),
        ];
    }

    /**
     * @return list<string>
     */
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
