<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\FileObservabilityRecordExporter;
use PHPUnit\Framework\TestCase;

final class FileObservabilityRecordExporterTest extends TestCase
{
    public function testExporterWritesStructuredNdjsonRecordIntoNamedStream(): void
    {
        $dir = sys_get_temp_dir() . '/vendoring-observability-' . bin2hex(random_bytes(4));
        $exporter = new FileObservabilityRecordExporter($dir);

        $exporter->export('runtime_logs', [
            'timestamp' => '2026-04-03T00:00:00+00:00',
            'level' => 'info',
            'message' => 'runtime_check',
        ]);

        $path = $dir . '/runtime_logs.ndjson';

        self::assertFileExists($path);
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        self::assertIsArray($lines);
        self::assertCount(1, $lines);

        /** @var array<string,mixed> $payload */
        $payload = json_decode((string) $lines[0], true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('info', $payload['level']);
        self::assertSame('runtime_check', $payload['message']);
    }
}
