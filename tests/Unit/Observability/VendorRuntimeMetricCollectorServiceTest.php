<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Observability;

use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorObservabilityRecordExporterService;
use App\Vendoring\Service\Observability\VendorRuntimeMetricCollectorService;
use App\Vendoring\Service\Runtime\VendorAppEnvResolverService;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeMetricCollectorServiceTest extends TestCase
{
    public function testCollectorCapturesStructuredMetricPayloadAndExportsIt(): void
    {
        $dir = sys_get_temp_dir() . '/vendoring-metrics-' . bin2hex(random_bytes(4));
        $correlationContext = new VendorCorrelationContextService();
        $correlationContext->beginRequest('corr-metric-1');
        $exporter = new VendorObservabilityRecordExporterService($dir);

        $collector = new VendorRuntimeMetricCollectorService($correlationContext, new VendorAppEnvResolverService(), $exporter);
        $collector->increment('statement_mail_sent_total', ['tenant' => 'tenant-1', 'vendor' => 'vendor-1']);

        $snapshot = $collector->snapshot();

        self::assertCount(1, $snapshot);
        self::assertSame('metric', $snapshot[0]['type']);
        self::assertSame('statement_mail_sent_total', $snapshot[0]['name']);
        self::assertSame('corr-metric-1', $snapshot[0]['request_id']);
        self::assertSame('tenant-1', $snapshot[0]['tags']['tenant']);
        self::assertFileExists($dir . '/runtime_metrics.ndjson');
    }

    public function testCollectorNormalizesEmptyTagsAndMissingCorrelationDeterministically(): void
    {
        $collector = new VendorRuntimeMetricCollectorService(new VendorCorrelationContextService(), new VendorAppEnvResolverService());
        $collector->increment('payout_processed_total');

        $snapshot = $collector->snapshot();

        self::assertCount(1, $snapshot);
        self::assertSame([], $snapshot[0]['tags']);
        self::assertNull($snapshot[0]['request_id']);
        self::assertNull($snapshot[0]['correlation_id']);
    }
}
