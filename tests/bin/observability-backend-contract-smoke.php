<?php

declare(strict_types=1);

use App\Observability\Service\CorrelationContext;
use App\Observability\Service\FileObservabilityRecordExporter;
use App\Observability\Service\RuntimeLogger;
use App\Observability\Service\RuntimeMetricCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$dir = sys_get_temp_dir() . '/vendoring-observability-smoke-' . bin2hex(random_bytes(4));
$exporter = new FileObservabilityRecordExporter($dir);
$correlationContext = new CorrelationContext();
$correlationContext->beginRequest('smoke-correlation-id');

$requestStack = new RequestStack();
$request = Request::create('/api/vendor-transactions');
$request->attributes->set('_route', 'app_vendor_transaction_create');
$requestStack->push($request);

$logger = new RuntimeLogger($correlationContext, $requestStack, $exporter);
$metrics = new RuntimeMetricCollector($correlationContext, $exporter);

$logger->info('observability_backend_smoke', ['vendor_id' => 'vendor-1']);
$metrics->increment('observability_backend_smoke_total', ['scope' => 'synthetic']);

$logPath = $dir . '/runtime_logs.ndjson';
$metricPath = $dir . '/runtime_metrics.ndjson';

if (!is_file($logPath) || !is_file($metricPath)) {
    fwrite(STDERR, "observability backend smoke failed: export files were not created\n");

    exit(1);
}

$logLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$metricLines = file($metricPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!is_array($logLines) || [] === $logLines || !is_array($metricLines) || [] === $metricLines) {
    fwrite(STDERR, "observability backend smoke failed: exported streams are empty\n");

    exit(1);
}

/** @var array<string,mixed> $logPayload */
$logPayload = json_decode((string) $logLines[0], true, flags: JSON_THROW_ON_ERROR);
/** @var array<string,mixed> $metricPayload */
$metricPayload = json_decode((string) $metricLines[0], true, flags: JSON_THROW_ON_ERROR);

if (($logPayload['correlation_id'] ?? null) !== 'smoke-correlation-id') {
    fwrite(STDERR, "observability backend smoke failed: log correlation id missing\n");

    exit(1);
}

if (($metricPayload['name'] ?? null) !== 'observability_backend_smoke_total') {
    fwrite(STDERR, "observability backend smoke failed: metric name mismatch\n");

    exit(1);
}

fwrite(STDOUT, "observability backend contract smoke passed\n");
exit(0);
