<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\MonitoringSnapshotBuilder;
use PHPUnit\Framework\TestCase;

final class MonitoringSnapshotBuilderTest extends TestCase
{
    public function testBuildAggregatesLogsMetricsBreakersAndProbeArtifacts(): void
    {
        $root = sys_get_temp_dir().'/vendoring-monitoring-'.bin2hex(random_bytes(4));
        $observabilityDir = $root.'/observability';
        $breakerDir = $root.'/fault-tolerance/circuit-breakers';
        $projectDir = $root.'/project';
        mkdir($observabilityDir, 0777, true);
        mkdir($breakerDir, 0777, true);
        mkdir($projectDir.'/docs', 0777, true);

        file_put_contents($observabilityDir.'/runtime_logs.ndjson', json_encode([
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'level' => 'error',
            'route' => 'vendor_transaction_create',
            'error_code' => 'invalid_api_token',
        ]).PHP_EOL);
        file_put_contents($observabilityDir.'/runtime_metrics.ndjson', json_encode([
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'name' => 'statement_mail_failed_total',
        ]).PHP_EOL);
        file_put_contents($breakerDir.'/mail.json', json_encode([
            'state' => 'open',
            'scopeKey' => 'tenant-1:vendor-1',
        ]));
        file_put_contents($projectDir.'/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md', "ok");
        file_put_contents($projectDir.'/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md', "ok");
        file_put_contents($projectDir.'/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md', "ok");
        file_put_contents($projectDir.'/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md', "ok");

        $builder = new MonitoringSnapshotBuilder($observabilityDir, $root.'/fault-tolerance', $projectDir);
        $snapshot = $builder->build(900);

        self::assertSame('warn', $snapshot['status']);
        self::assertSame(1, $snapshot['logSummary']['error']);
        self::assertContains('vendor_transaction_create', $snapshot['logSummary']['routes']);
        self::assertContains('invalid_api_token', $snapshot['logSummary']['errorCodes']);
        self::assertSame(1, $snapshot['metricSummary']['total']);
        self::assertSame(1, $snapshot['metricSummary']['names']['statement_mail_failed_total']);
        self::assertSame(1, $snapshot['breakerSummary']['open']);
        self::assertContains('tenant-1:vendor-1', $snapshot['breakerSummary']['scopes']);
        self::assertTrue($snapshot['probeSummary']['transaction']);
        self::assertTrue($snapshot['probeSummary']['finance']);
        self::assertTrue($snapshot['probeSummary']['payout']);
        self::assertTrue($snapshot['probeSummary']['postDeploy']);
    }
}
