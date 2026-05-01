<?php

declare(strict_types=1);

use App\Vendoring\Service\Rollout\VendorCanaryRolloutCoordinatorService;
use App\Vendoring\Service\Rollout\VendorFeatureFlagService;
use App\Vendoring\Service\Rollout\VendorTrafficCohortResolverService;
use App\Vendoring\Service\Ops\VendorReleaseManifestBuilderService;
use App\Vendoring\Service\Ops\VendorRollbackDecisionEvaluatorService;
use App\Vendoring\Service\Observability\VendorMonitoringSnapshotBuilderService;
use App\Vendoring\Service\Observability\VendorAlertRuleEvaluatorService;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$observabilityDir = $root . '/var/observability';
$faultToleranceDir = $root . '/var/fault-tolerance';

@mkdir($observabilityDir, 0777, true);
@mkdir($faultToleranceDir . '/circuit-breakers', 0777, true);
@mkdir($root . '/docs/release', 0777, true);
@mkdir($root . '/build/release', 0777, true);
@mkdir($root . '/build/docs/phpdocumentor', 0777, true);
@mkdir($root . '/docs', 0777, true);

file_put_contents($observabilityDir . '/runtime_logs.ndjson', json_encode(['timestamp' => date(DATE_ATOM), 'level' => 'info', 'message' => 'canary smoke']) . PHP_EOL);
file_put_contents($observabilityDir . '/runtime_metrics.ndjson', json_encode(['timestamp' => date(DATE_ATOM), 'type' => 'counter', 'name' => 'canary_smoke_metric', 'tags' => ['scope' => 'smoke'], 'request_id' => 'smoke', 'correlation_id' => 'smoke']) . PHP_EOL);

foreach ([
    $root . '/docs/release/RC_BASELINE.md',
    $root . '/docs/release/RC_RUNTIME_SURFACES.md',
    $root . '/docs/release/RC_OPERATOR_SURFACE.md',
    $root . '/docs/release/RC_EVIDENCE_PACK.md',
    $root . '/docs/release/RC_ROLLBACK_MANIFEST.md',
    $root . '/docs/release/RC_RELEASE_MANIFEST.md',
    $root . '/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md',
    $root . '/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md',
    $root . '/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md',
    $root . '/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md',
    $root . '/build/release/rc-evidence.json',
    $root . '/build/release/rc-evidence.md',
    $root . '/build/release/release-manifest.json',
    $root . '/build/release/rollback-manifest.json',
    $root . '/build/docs/phpdocumentor/index.html',
] as $file) {
    if (!is_file($file)) {
        file_put_contents($file, 'placeholder');
    }
}

$featureFlags = [
    'transaction_canary' => [
        'enabled' => false,
        'cohorts' => ['vendor:42'],
    ],
];

$coordinator = new VendorCanaryRolloutCoordinatorService(
    new VendorFeatureFlagService(new VendorTrafficCohortResolverService(), $featureFlags),
    new VendorTrafficCohortResolverService(),
    new VendorReleaseManifestBuilderService(
        new VendorMonitoringSnapshotBuilderService($observabilityDir, $faultToleranceDir, $root),
        new VendorAlertRuleEvaluatorService(),
        $root,
    ),
    new VendorRollbackDecisionEvaluatorService(),
);

$report = $coordinator->evaluate('transaction_canary', 'tenant-1', '42', 900);

if ($report['canary']['decision'] !== 'proceed') {
    throw new RuntimeException('Canary rollout coordinator did not return proceed for a green vendor canary.');
}
if ($report['canary']['recommendedAction'] !== 'expand_canary_scope') {
    throw new RuntimeException('Canary rollout coordinator did not recommend expansion.');
}
if (($report['canary']['nextCohort'] ?? null) !== 'tenant:tenant-1') {
    throw new RuntimeException('Canary rollout coordinator did not suggest tenant expansion.');
}
if ($report['flagDecision']['cohort'] !== 'vendor:42') {
    throw new RuntimeException('Canary rollout coordinator did not preserve vendor cohort.');
}

echo "canary rollout contract smoke passed\n";
