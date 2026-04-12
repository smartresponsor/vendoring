<?php

declare(strict_types=1);

use App\Observability\Service\AlertRuleEvaluator;
use App\Observability\Service\MonitoringSnapshotBuilder;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = sys_get_temp_dir() . '/vendoring-monitoring-smoke-' . bin2hex(random_bytes(4));
$observabilityDir = $root . '/observability';
$faultToleranceDir = $root . '/fault-tolerance';
$breakerDir = $faultToleranceDir . '/circuit-breakers';
$projectDir = $root . '/project';
mkdir($observabilityDir, 0777, true);
mkdir($breakerDir, 0777, true);
mkdir($projectDir . '/docs', 0777, true);

file_put_contents($observabilityDir . '/runtime_logs.ndjson', json_encode([
    'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
    'level' => 'error',
    'route' => 'vendor_transaction_create',
    'error_code' => 'authentication_required',
], JSON_THROW_ON_ERROR) . PHP_EOL);
file_put_contents($observabilityDir . '/runtime_metrics.ndjson', json_encode([
    'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
    'name' => 'runtime_probe_total',
], JSON_THROW_ON_ERROR) . PHP_EOL);
file_put_contents($breakerDir . '/mail.json', json_encode([
    'state' => 'open',
    'scopeKey' => 'tenant-1:vendor-1',
], JSON_THROW_ON_ERROR));
file_put_contents($projectDir . '/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md', 'ok');
file_put_contents($projectDir . '/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md', 'ok');
file_put_contents($projectDir . '/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md', 'ok');
file_put_contents($projectDir . '/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md', 'ok');

$builder = new MonitoringSnapshotBuilder($observabilityDir, $faultToleranceDir, $projectDir);
$snapshot = $builder->build(900);
$alerts = (new AlertRuleEvaluator())->evaluate($snapshot);

if ('warn' !== ($snapshot['status'] ?? null)) {
    fwrite(STDERR, "monitoring alerting smoke failed: snapshot status mismatch\n");
    exit(1);
}

if (1 !== ($snapshot['breakerSummary']['open'] ?? null)) {
    fwrite(STDERR, "monitoring alerting smoke failed: breaker summary mismatch\n");
    exit(1);
}

$codes = array_map(static fn(array $alert): string => (string) ($alert['code'] ?? ''), $alerts);
if (!in_array('runtime_error_spike', $codes, true) || !in_array('outbound_circuit_open', $codes, true)) {
    fwrite(STDERR, "monitoring alerting smoke failed: expected alerts missing\n");
    exit(1);
}

fwrite(STDOUT, "monitoring alerting contract smoke passed\n");
