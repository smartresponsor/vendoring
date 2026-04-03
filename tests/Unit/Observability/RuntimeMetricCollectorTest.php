<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\CorrelationContext;
use App\Observability\Service\FileObservabilityRecordExporter;
use App\Observability\Service\RuntimeMetricCollector;
use PHPUnit\Framework\TestCase;

final class RuntimeMetricCollectorTest extends TestCase
{
    public function testCollectorCapturesStructuredMetricPayloadAndExportsIt(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-metrics-'.bin2hex(random_bytes(4));
        $correlationContext = new CorrelationContext();
        $correlationContext->beginRequest('corr-metric-1');
        $exporter = new FileObservabilityRecordExporter($dir);

        $collector = new RuntimeMetricCollector($correlationContext, $exporter);
        $collector->increment('statement_mail_sent_total', ['tenant' => 'tenant-1', 'vendor' => 'vendor-1']);

        $snapshot = $collector->snapshot();

        self::assertCount(1, $snapshot);
        self::assertSame('metric', $snapshot[0]['type']);
        self::assertSame('statement_mail_sent_total', $snapshot[0]['name']);
        self::assertSame('corr-metric-1', $snapshot[0]['request_id']);
        self::assertSame('tenant-1', $snapshot[0]['tags']['tenant']);
        self::assertFileExists($dir.'/runtime_metrics.ndjson');
    }

    public function testCollectorNormalizesEmptyTagsAndMissingCorrelationDeterministically(): void
    {
        $collector = new RuntimeMetricCollector(new CorrelationContext());
        $collector->increment('payout_processed_total');

        $snapshot = $collector->snapshot();

        self::assertCount(1, $snapshot);
        self::assertSame([], $snapshot[0]['tags']);
        self::assertNull($snapshot[0]['request_id']);
        self::assertNull($snapshot[0]['correlation_id']);
    }
}
