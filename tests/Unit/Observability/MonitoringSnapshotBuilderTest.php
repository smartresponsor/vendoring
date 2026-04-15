<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\MonitoringSnapshotBuilder;
use PHPUnit\Framework\TestCase;

final class MonitoringSnapshotBuilderTest extends TestCase
{
    public function testBuildAggregatesLogsMetricsBreakersAndProbeArtifacts(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $breakerDir = $root . '/fault-tolerance/circuit-breakers';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($breakerDir, 0777, true) || is_dir($breakerDir));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents($observabilityDir . '/runtime_logs.ndjson', $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'error',
                'route' => 'vendor_transaction_create',
                'error_code' => 'invalid_api_token',
            ]) . PHP_EOL));
            self::assertNotFalse(file_put_contents($observabilityDir . '/runtime_metrics.ndjson', $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'name' => 'statement_mail_failed_total',
            ]) . PHP_EOL));
            self::assertNotFalse(file_put_contents($breakerDir . '/mail.json', $this->encodeJson([
                'state' => 'open',
                'scopeKey' => 'tenant-1:vendor-1',
            ])));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md', 'ok'));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
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
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildMarksMissingProbeArtifactsAsFalseAndWarn(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-missing-probes-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents($observabilityDir . '/runtime_logs.ndjson', $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'info',
                'route' => 'vendor_runtime_status',
            ]) . PHP_EOL));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame('warn', $snapshot['status']);
            self::assertFalse($snapshot['probeSummary']['transaction']);
            self::assertFalse($snapshot['probeSummary']['finance']);
            self::assertFalse($snapshot['probeSummary']['payout']);
            self::assertFalse($snapshot['probeSummary']['postDeploy']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildMarksWarningStatusWhenWarningLogsPresent(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-warning-status-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents($observabilityDir . '/runtime_logs.ndjson', $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'warning',
                'route' => 'vendor_transaction_status_update',
            ]) . PHP_EOL));

            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md', 'ok'));
            self::assertNotFalse(file_put_contents($projectDir . '/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md', 'ok'));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertSame('warn', $snapshot['status']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildIgnoresInvalidAndBlankNdjsonLines(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-ndjson-filter-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $validError = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'error',
                'route' => 'vendor_transaction_create',
                'error_code' => 'duplicate_transaction',
            ]);
            $validWarning = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'warning',
                'route' => 'vendor_transaction_update',
            ]);

            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $validError . PHP_EOL . '{not-json' . PHP_EOL . PHP_EOL . $validWarning . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(2, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['error']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertContains('duplicate_transaction', $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUtf8BomPrefixedNdjsonLine(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-bom-line-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $payload = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'level' => 'error',
                'route' => 'vendor_transaction_create',
                'error_code' => 'duplicate_transaction',
            ]);

            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                "\xEF\xBB\xBF" . $payload . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['error']);
            self::assertSame(['duplicate_transaction'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUtf8BomPrefixedMetricsNdjsonLine(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-bom-metric-line-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $payload = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'name' => 'statement_mail_failed_total',
            ]);

            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_metrics.ndjson',
                "\xEF\xBB\xBF" . $payload . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['metricSummary']['total']);
            self::assertSame(1, $snapshot['metricSummary']['names']['statement_mail_failed_total']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildReturnsSortedUniqueLogRoutesAndErrorCodes(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-log-sort-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    'level' => 'warning',
                    'route' => 'z-route',
                    'error_code' => 'z-error',
                ]) . PHP_EOL .
                $this->encodeJson([
                    'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    'level' => 'warning',
                    'route' => 'a-route',
                    'error_code' => 'a-error',
                ]) . PHP_EOL .
                $this->encodeJson([
                    'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    'level' => 'warning',
                    'route' => 'z-route',
                    'error_code' => 'z-error',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(['a-route', 'z-route'], $snapshot['logSummary']['routes']);
            self::assertSame(['a-error', 'z-error'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildNormalizesNonPositiveWindowSeconds(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-window-normalize-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(-5);

            self::assertSame(1, $snapshot['windowSeconds']);
            self::assertStringEndsWith('+00:00', $snapshot['generatedAt']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildIgnoresInvalidMetricLinesAndCountsValidNames(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-metric-ndjson-filter-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $metricA = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'name' => 'statement_mail_failed_total',
            ]);
            $metricB = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'name' => 'statement_mail_failed_total',
            ]);
            $metricC = $this->encodeJson([
                'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                'name' => 'payout_processing_failed_total',
            ]);

            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_metrics.ndjson',
                $metricA . PHP_EOL . '{not-json' . PHP_EOL . PHP_EOL . $metricB . PHP_EOL . $metricC . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(3, $snapshot['metricSummary']['total']);
            self::assertSame(2, $snapshot['metricSummary']['names']['statement_mail_failed_total']);
            self::assertSame(1, $snapshot['metricSummary']['names']['payout_processing_failed_total']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsRecordsWithUnparseableTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-invalid-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => 'not-a-date',
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(1);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['warning']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsRecordsWithNonIsoTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-non-iso-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '2026-04-14 11:00:00',
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['warning']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsIsoTimestampsWithMicroseconds(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-microsecond-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s.uP'),
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertSame(['status_required'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsZuluIsoTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-zulu-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['error']);
            self::assertSame(['duplicate_transaction'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsTrimmedIsoTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-trimmed-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $timestamp = (new \DateTimeImmutable())->format(DATE_ATOM);
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '  ' . $timestamp . '  ',
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUnixTimestampValues(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-unix-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => time(),
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertSame(['status_required'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUnixTimestampStringWithFractionalSeconds(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-unix-fractional-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $epochWithFraction = sprintf('%.3f', (float) time() + 0.625);
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => $epochWithFraction,
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['error']);
            self::assertSame(['duplicate_transaction'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUnixTimestampStringInScientificNotation(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-unix-scientific-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            $scientificEpoch = sprintf('%.6E', (float) time());
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => $scientificEpoch,
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertSame(['status_required'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsOverflowedNumericTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-overflow-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '1e309',
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['error']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsOverflowedIntegerTimestampStrings(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-overflow-int-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '922337203685477580799',
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['error']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsNegativeUnixTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-negative-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => -5,
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['warning']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAcceptsUnixTimestampStringWithLeadingPlus(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-plus-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '+' . (string) time(),
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['logSummary']['total']);
            self::assertSame(1, $snapshot['logSummary']['warning']);
            self::assertSame(['status_required'], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsRecordsWithMissingTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-missing-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['error']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsRecordsWithEmptyTimestamp(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-empty-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => '',
                    'level' => 'warning',
                    'route' => 'vendor_transaction_status_update',
                    'error_code' => 'status_required',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['warning']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildSkipsRecordsWithFutureTimestamps(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-future-timestamp-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($root . '/fault-tolerance/circuit-breakers', 0777, true) || is_dir($root . '/fault-tolerance/circuit-breakers'));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents(
                $observabilityDir . '/runtime_logs.ndjson',
                $this->encodeJson([
                    'timestamp' => (new \DateTimeImmutable('+2 days'))->format(DATE_ATOM),
                    'level' => 'error',
                    'route' => 'vendor_transaction_create',
                    'error_code' => 'duplicate_transaction',
                ]) . PHP_EOL,
            ));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(0, $snapshot['logSummary']['total']);
            self::assertSame(0, $snapshot['logSummary']['error']);
            self::assertSame([], $snapshot['logSummary']['errorCodes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testBuildAggregatesBreakerStatesAndIgnoresInvalidBreakerPayloads(): void
    {
        $root = sys_get_temp_dir() . '/vendoring-monitoring-breakers-' . bin2hex(random_bytes(4));
        $observabilityDir = $root . '/observability';
        $breakerDir = $root . '/fault-tolerance/circuit-breakers';
        $projectDir = $root . '/project';
        self::assertTrue(mkdir($observabilityDir, 0777, true) || is_dir($observabilityDir));
        self::assertTrue(mkdir($breakerDir, 0777, true) || is_dir($breakerDir));
        self::assertTrue(mkdir($projectDir . '/docs', 0777, true) || is_dir($projectDir . '/docs'));

        try {
            self::assertNotFalse(file_put_contents($breakerDir . '/z-open.json', $this->encodeJson([
                'state' => 'open',
                'scopeKey' => 'tenant-b:vendor-2',
            ])));
            self::assertNotFalse(file_put_contents($breakerDir . '/a-half-open.json', $this->encodeJson([
                'state' => 'half_open',
                'scopeKey' => 'tenant-a:vendor-1',
            ])));
            self::assertNotFalse(file_put_contents($breakerDir . '/m-closed.json', $this->encodeJson([
                'state' => 'closed',
                'scopeKey' => 'tenant-c:vendor-3',
            ])));
            self::assertNotFalse(file_put_contents($breakerDir . '/invalid.json', '{not-json'));

            $builder = new MonitoringSnapshotBuilder($observabilityDir, $root . '/fault-tolerance', $projectDir);
            $snapshot = $builder->build(900);

            self::assertSame(1, $snapshot['breakerSummary']['open']);
            self::assertSame(1, $snapshot['breakerSummary']['halfOpen']);
            self::assertSame(1, $snapshot['breakerSummary']['closed']);
            self::assertSame(['tenant-b:vendor-2'], $snapshot['breakerSummary']['scopes']);
        } finally {
            $this->removeDirectory($root);
        }
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_link($itemPath)) {
                unlink($itemPath);
                continue;
            }

            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
                continue;
            }

            if (is_file($itemPath)) {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }

    /**
     * @param array<string, scalar|null> $payload
     */
    private function encodeJson(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
