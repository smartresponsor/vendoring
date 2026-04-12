<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\AlertRuleEvaluatorInterface;

/**
 * Deterministic alert evaluator for monitoring snapshots.
 *
 * The evaluator converts snapshot counters into explicit warning/critical alerts without
 * calling remote monitoring systems.
 */
final readonly class AlertRuleEvaluator implements AlertRuleEvaluatorInterface
{
    /**
     * @param array{errorLogThreshold?:int,openBreakerThreshold?:int,missingProbeThreshold?:int} $thresholds
     */
    public function __construct(private array $thresholds = []) {}

    public function evaluate(array $snapshot): array
    {
        $alerts = [];

        $errorThreshold = (int) ($this->thresholds['errorLogThreshold'] ?? 1);
        $openBreakerThreshold = (int) ($this->thresholds['openBreakerThreshold'] ?? 1);
        $missingProbeThreshold = (int) ($this->thresholds['missingProbeThreshold'] ?? 1);

        if (($snapshot['logSummary']['error'] ?? 0) >= $errorThreshold) {
            $alerts[] = [
                'code' => 'runtime_error_spike',
                'severity' => 'warning',
                'message' => sprintf('Runtime error count reached %d within the monitoring window.', (int) $snapshot['logSummary']['error']),
                'context' => ['errorCodes' => $snapshot['logSummary']['errorCodes'] ?? []],
            ];
        }

        if (($snapshot['breakerSummary']['open'] ?? 0) >= $openBreakerThreshold) {
            $alerts[] = [
                'code' => 'outbound_circuit_open',
                'severity' => 'critical',
                'message' => sprintf('Open outbound breakers detected: %d.', (int) $snapshot['breakerSummary']['open']),
                'context' => ['scopes' => $snapshot['breakerSummary']['scopes'] ?? []],
            ];
        }

        $missingProbes = array_keys(array_filter(
            $snapshot['probeSummary'] ?? [],
            static fn(bool $present): bool => false === $present,
        ));
        if (count($missingProbes) >= $missingProbeThreshold) {
            $alerts[] = [
                'code' => 'probe_artifacts_missing',
                'severity' => 'warning',
                'message' => 'One or more synthetic probe artifacts are missing.',
                'context' => ['missing' => array_values($missingProbes)],
            ];
        }

        if (0 === (int) ($snapshot['metricSummary']['total'] ?? 0)) {
            $alerts[] = [
                'code' => 'observability_metrics_empty',
                'severity' => 'warning',
                'message' => 'No runtime metrics were exported in the monitoring window.',
                'context' => [],
            ];
        }

        return $alerts;
    }
}
