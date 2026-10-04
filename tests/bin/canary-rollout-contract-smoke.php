<?php

declare(strict_types=1);

use App\Vendoring\Builder\Observability\VendorMonitoringSnapshotBuilder;
use App\Vendoring\Builder\Ops\VendorReleaseManifestBuilder;
use App\Vendoring\Resolver\Rollout\VendorTrafficCohortResolver;
use App\Vendoring\Service\Observability\VendorAlertRuleEvaluatorService;
use App\Vendoring\Service\Ops\VendorRollbackDecisionEvaluatorService;
use App\Vendoring\Service\Rollout\VendorCanaryRolloutCoordinatorService;
use App\Vendoring\Service\Rollout\VendorFeatureFlagService;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$smokeStateDir = sys_get_temp_dir().'/vendoring-canary-smoke-'.bin2hex(random_bytes(4));
$observabilityDir = $smokeStateDir.'/observability';
$faultToleranceDir = $smokeStateDir.'/fault-tolerance';

@mkdir($observabilityDir, 0777, true);
@mkdir($faultToleranceDir.'/circuit-breakers', 0777, true);
@mkdir($root.'/docs/release', 0777, true);
@mkdir($root.'/build/release', 0777, true);
@mkdir($root.'/build/docs/phpdocumentor', 0777, true);
@mkdir($root.'/docs', 0777, true);

file_put_contents($observabilityDir.'/runtime_logs.ndjson', json_encode(['timestamp' => date(DATE_ATOM), 'level' => 'info', 'message' => 'canary smoke']).PHP_EOL);
file_put_contents($observabilityDir.'/runtime_metrics.ndjson', json_encode(['timestamp' => date(DATE_ATOM), 'type' => 'counter', 'nameEntity' => 'canary_smoke_metric', 'tags' => ['scope' => 'smoke'], 'request_id' => 'smoke', 'correlation_id' => 'smoke']).PHP_EOL);

foreach ([
    $root.'/docs/release/RC_BASELINE.md',
    $root.'/docs/release/RC_RUNTIME_SURFACES.md',
    $root.'/docs/release/RC_OPERATOR_SURFACE.md',
    $root.'/docs/release/RC_EVIDENCE_PACK.md',
    $root.'/docs/release/RC_ROLLBACK_MANIFEST.md',
    $root.'/docs/release/RC_RELEASE_MANIFEST.md',
    $root.'/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md',
    $root.'/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md',
    $root.'/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md',
    $root.'/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md',
    $root.'/build/release/rc-evidence.json',
    $root.'/build/release/rc-evidence.md',
    $root.'/build/release/release-manifest.json',
    $root.'/build/release/rollback-manifest.json',
    $root.'/build/docs/phpdocumentor/index.html',
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
    new VendorFeatureFlagService(new VendorTrafficCohortResolver(), $featureFlags),
    new VendorTrafficCohortResolver(),
    new VendorReleaseManifestBuilder(
        new VendorMonitoringSnapshotBuilder($observabilityDir, $faultToleranceDir, $root),
        new VendorAlertRuleEvaluatorService(),
        $root,
    ),
    new VendorRollbackDecisionEvaluatorService(),
);

$report = $coordinator->evaluate('transaction_canary', '42', 900);

if ('proceed' !== $report['canary']['decision']) {
    throw new RuntimeException('Canary rollout coordinator did not return proceed for a green vendor canary.');
}
if ('expand_canary_scope' !== $report['canary']['recommendedAction']) {
    throw new RuntimeException('Canary rollout coordinator did not recommend expansion.');
}
if (($report['canary']['nextCohort'] ?? null) !== 'global') {
    throw new RuntimeException('Canary rollout coordinator did not suggest global expansion.');
}
if ('vendor:42' !== $report['flagDecision']['cohort']) {
    throw new RuntimeException('Canary rollout coordinator did not preserve vendor cohort.');
}

echo "canary rollout contract smoke passed\n";
