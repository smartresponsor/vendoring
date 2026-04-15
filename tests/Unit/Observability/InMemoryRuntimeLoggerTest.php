<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Tests\Support\Observability\InMemoryRuntimeLogger;
use PHPUnit\Framework\TestCase;

final class InMemoryRuntimeLoggerTest extends TestCase
{
    public function testWarningRecordContainsDefaultEnvelopeFields(): void
    {
        $logger = new InMemoryRuntimeLogger();
        $logger->warning('vendor_transaction_create_rejected', ['vendor_id' => 'vendor-42']);

        $records = $logger->snapshot();
        self::assertCount(1, $records);

        $record = $records[0];
        self::assertSame('warning', $record['level']);
        self::assertSame('vendor_transaction_create_rejected', $record['message']);
        self::assertSame('vendor-42', $record['vendor_id']);
        self::assertArrayHasKey('timestamp', $record);
        self::assertIsString($record['timestamp']);
        self::assertNotFalse(\DateTimeImmutable::createFromFormat(DATE_ATOM, $record['timestamp']));
        self::assertArrayHasKey('request_id', $record);
        self::assertArrayHasKey('correlation_id', $record);
        self::assertArrayHasKey('route', $record);
        self::assertArrayHasKey('path', $record);
        self::assertArrayHasKey('transaction_id', $record);
        self::assertArrayHasKey('error_code', $record);
    }

    public function testBooleanContextValuesAreNormalizedToStrings(): void
    {
        $logger = new InMemoryRuntimeLogger();
        $logger->info('probe_status', ['probe_active' => true, 'fallback' => false]);

        $records = $logger->snapshot();
        self::assertCount(1, $records);

        $record = $records[0];
        self::assertSame('true', $record['probe_active']);
        self::assertSame('false', $record['fallback']);
    }
}
