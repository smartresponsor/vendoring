<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\AlertRuleEvaluator;
use PHPUnit\Framework\TestCase;

final class AlertRuleEvaluatorTest extends TestCase
{
    public function testEvaluateReturnsAlertsForErrorsBreakersMissingProbesAndEmptyMetrics(): void
    {
        $evaluator = new AlertRuleEvaluator([
            'errorLogThreshold' => 1,
            'openBreakerThreshold' => 1,
            'missingProbeThreshold' => 1,
        ]);

        $alerts = $evaluator->evaluate([
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'windowSeconds' => 900,
            'logSummary' => ['total' => 2, 'error' => 1, 'warning' => 0, 'routes' => [], 'errorCodes' => ['invalid_api_token']],
            'metricSummary' => ['total' => 0, 'names' => []],
            'breakerSummary' => ['open' => 1, 'halfOpen' => 0, 'closed' => 0, 'scopes' => ['tenant-1:vendor-1']],
            'probeSummary' => ['transaction' => true, 'finance' => false, 'payout' => true, 'postDeploy' => true],
            'status' => 'warn',
        ]);

        self::assertCount(4, $alerts);
        self::assertSame('runtime_error_spike', $alerts[0]['code']);
        self::assertSame('outbound_circuit_open', $alerts[1]['code']);
        self::assertSame('probe_artifacts_missing', $alerts[2]['code']);
        self::assertSame('observability_metrics_empty', $alerts[3]['code']);
    }
}
