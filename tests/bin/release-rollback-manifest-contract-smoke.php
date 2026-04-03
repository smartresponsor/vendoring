<?php

declare(strict_types=1);

use App\Observability\Service\AlertRuleEvaluator;
use App\Observability\Service\MonitoringSnapshotBuilder;
use App\Service\Ops\ReleaseManifestBuilder;
use App\Service\Ops\RollbackDecisionEvaluator;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$projectDir = dirname(__DIR__, 2);
$observabilityDir = $projectDir.'/var/observability';
$faultToleranceDir = $projectDir.'/var/fault-tolerance';
$breakerDir = $faultToleranceDir.'/circuit-breakers';

@mkdir($observabilityDir, 0777, true);
@mkdir($breakerDir, 0777, true);
@mkdir($projectDir.'/build/release', 0777, true);
@mkdir($projectDir.'/build/docs/phpdocumentor', 0777, true);

file_put_contents($observabilityDir.'/runtime_logs.ndjson', json_encode([
    'timestamp' => date(DATE_ATOM),
    'level' => 'error',
    'route' => '/api/vendor-transactions',
    'error_code' => 'statement_mail_circuit_open',
], JSON_THROW_ON_ERROR).PHP_EOL);
file_put_contents($observabilityDir.'/runtime_metrics.ndjson', json_encode([
    'timestamp' => date(DATE_ATOM),
    'name' => 'vendor.transaction.create',
], JSON_THROW_ON_ERROR).PHP_EOL);
file_put_contents($breakerDir.'/statement_mail_send__tenant_1_vendor_42.json', json_encode([
    'operation' => 'statement_mail_send',
    'scopeKey' => 'tenant:1:vendor:42',
    'state' => 'open',
], JSON_THROW_ON_ERROR));

foreach ([
    'docs/release/RC_BASELINE.md',
    'docs/release/RC_RUNTIME_SURFACES.md',
    'docs/release/RC_OPERATOR_SURFACE.md',
    'docs/release/RC_EVIDENCE_PACK.md',
    'docs/release/RC_ROLLBACK_MANIFEST.md',
    'docs/release/RC_RELEASE_MANIFEST.md',
] as $relative) {
    if (!is_file($projectDir.'/'.$relative)) {
        throw new RuntimeException('Missing required release doc: '.$relative);
    }
}
file_put_contents($projectDir.'/build/release/rc-evidence.json', '{}');
file_put_contents($projectDir.'/build/release/rc-evidence.md', '# ok');
file_put_contents($projectDir.'/build/docs/phpdocumentor/index.html', '<html></html>');

$builder = new ReleaseManifestBuilder(
    new MonitoringSnapshotBuilder($observabilityDir, $faultToleranceDir, $projectDir),
    new AlertRuleEvaluator(),
    $projectDir,
);
$manifest = $builder->build(900);
$rollback = (new RollbackDecisionEvaluator())->evaluate($manifest);

if ('rollback' !== $rollback['decision']) {
    throw new RuntimeException('Expected rollback decision for open breaker manifest.');
}
if (!in_array('outbound_circuit_open', $manifest['monitoring']['alertCodes'], true)) {
    throw new RuntimeException('Expected outbound_circuit_open alert in release manifest.');
}

echo "release rollback manifest contract smoke passed\n";
