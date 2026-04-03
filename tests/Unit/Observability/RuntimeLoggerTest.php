<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\CorrelationContext;
use App\Observability\Service\RuntimeLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RuntimeLoggerTest extends TestCase
{
    public function testRuntimeLoggerCapturesStructuredPayloadWithRequestContext(): void
    {
        $requestStack = new RequestStack();
        $request = Request::create('/api/vendor-transactions');
        $request->attributes->set('_route', 'app_vendor_transaction_create');
        $requestStack->push($request);

        $correlationContext = new CorrelationContext();
        $correlationContext->beginRequest('corr-123');

        $logger = new RuntimeLogger($correlationContext, $requestStack);
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
}
