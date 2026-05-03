<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Observability;

use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorObservabilityRecordExporterService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class VendorRuntimeLoggerServiceTest extends TestCase
{
    public function testRuntimeLoggerCapturesStructuredPayloadWithRequestContext(): void
    {
        $requestStack = new RequestStack();
        $request = Request::create('/api/vendor-transactions');
        $request->attributes->set('_route', 'app_vendor_transaction_create');
        $requestStack->push($request);

        $correlationContext = new VendorCorrelationContextService();
        $correlationContext->beginRequest('corr-123');

        $logger = new VendorRuntimeLoggerService($correlationContext, $requestStack);
        $logger->warning('vendor_transaction_create_rejected', [
            'vendor_id' => 'vendor-1',
            'error_code' => 'duplicate_transaction',
            'status_code' => '409',
        ]);

        $records = $logger->snapshot();

        self::assertCount(1, $records);
        self::assertSame('warning', $records[0]['level']);
        self::assertSame('vendor_transaction_create_rejected', $records[0]['message']);
        self::assertSame('corr-123', $records[0]['correlation_id']);
        self::assertSame('app_vendor_transaction_create', $records[0]['route']);
        self::assertSame('/api/vendor-transactions', $records[0]['path']);
        self::assertSame('vendor-1', $records[0]['vendor_id']);
        self::assertSame('duplicate_transaction', $records[0]['error_code']);
        self::assertSame('409', $records[0]['status_code']);
        self::assertArrayHasKey('timestamp', $records[0]);
    }

    public function testRuntimeLoggerExportsStructuredRecordIntoFileBackend(): void
    {
        $requestStack = new RequestStack();
        $request = Request::create('/api/vendor-transactions');
        $request->attributes->set('_route', 'app_vendor_transaction_create');
        $requestStack->push($request);

        $correlationContext = new VendorCorrelationContextService();
        $correlationContext->beginRequest('corr-log-1');

        $dir = sys_get_temp_dir() . '/vendoring-logs-' . bin2hex(random_bytes(4));
        $exporter = new VendorObservabilityRecordExporterService($dir);

        $logger = new VendorRuntimeLoggerService($correlationContext, $requestStack, $exporter);
        $logger->info('vendor_transaction_created', ['vendor_id' => 'vendor-1']);

        $path = $dir . '/runtime_logs.ndjson';

        self::assertFileExists($path);
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        self::assertIsArray($lines);
        self::assertCount(1, $lines);

        /** @var array<string,mixed> $payload */
        $payload = json_decode((string) $lines[0], true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('vendor_transaction_created', $payload['message']);
        self::assertSame('corr-log-1', $payload['correlation_id']);
        self::assertSame('vendor-1', $payload['vendor_id']);
    }
}
