<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Observability;

use App\Vendoring\ServiceInterface\Observability\VendorAlertRuleEvaluatorServiceInterface;

/**
 * Deterministic alert evaluator for monitoring snapshots.
 *
 * Converts snapshot counters into explicit warning/critical alerts without
 * calling remote monitoring systems.
 *
 * Default thresholds are production-safe:
 *   - errorLogThreshold: 5  — single errors are noise; 5+ in a window is a spike
 *   - openBreakerThreshold: 1  — any open breaker is critical
 *   - missingProbeThreshold: 1  — any missing probe must be investigated
 *
 * Override via vendoring_alert_thresholds parameter or runtime.yaml:
 *
 *   vendoring_alert_thresholds:
 *       errorLogThreshold: 10
 *       openBreakerThreshold: 1
 *       missingProbeThreshold: 2
 */
final readonly class VendorAlertRuleEvaluatorService implements VendorAlertRuleEvaluatorServiceInterface
{
    private const int DEFAULT_ERROR_LOG_THRESHOLD = 5;
    private const int DEFAULT_OPEN_BREAKER_THRESHOLD = 1;
    private const int DEFAULT_MISSING_PROBE_THRESHOLD = 1;

    /**
     * @param array{errorLogThreshold?:int,openBreakerThreshold?:int,missingProbeThreshold?:int} $thresholds
     */
    public function __construct(private array $thresholds = [])
    {
    }

    public function evaluate(array $snapshot): array
    {
        $alerts = [];

        $errorThreshold = max(1, (int) ($this->thresholds['errorLogThreshold'] ?? self::DEFAULT_ERROR_LOG_THRESHOLD));
        $openBreakerThreshold = max(1, (int) ($this->thresholds['openBreakerThreshold'] ?? self::DEFAULT_OPEN_BREAKER_THRESHOLD));
        $missingProbeThreshold = max(1, (int) ($this->thresholds['missingProbeThreshold'] ?? self::DEFAULT_MISSING_PROBE_THRESHOLD));

        if ($snapshot['logSummary']['error'] >= $errorThreshold) {
            $alerts[] = [
                'code' => 'runtime_error_spike',
                'severity' => 'warning',
                'message' => sprintf(
                    'Runtime error count %d reached threshold %d within the monitoring window.',
                    $snapshot['logSummary']['error'],
                    $errorThreshold,
                ),
                'context' => ['errorCodes' => $snapshot['logSummary']['errorCodes']],
            ];
        }

        if ($snapshot['breakerSummary']['open'] >= $openBreakerThreshold) {
            $alerts[] = [
                'code' => 'outbound_circuit_open',
                'severity' => 'critical',
                'message' => sprintf('Open outbound breakers detected: %d.', $snapshot['breakerSummary']['open']),
                'context' => ['scopes' => $snapshot['breakerSummary']['scopes']],
            ];
        }

        $missingProbes = array_keys(array_filter(
            $snapshot['probeSummary'],
            static fn (bool $present): bool => false === $present,
        ));

        if (count($missingProbes) >= $missingProbeThreshold) {
            $alerts[] = [
                'code' => 'probe_artifacts_missing',
                'severity' => 'warning',
                'message' => 'One or more synthetic probe artifacts are missing.',
                'context' => ['missing' => $missingProbes],
            ];
        }

        if (0 === $snapshot['metricSummary']['total']) {
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
