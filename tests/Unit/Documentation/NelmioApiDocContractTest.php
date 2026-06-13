<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class NelmioApiDocContractTest extends TestCase
{
    public function testNelmioApiDocPackageConfigDefinesNativeSurface(): void
    {
        $path = dirname(__DIR__, 3) . '/config/packages/nelmio_api_doc.yaml';
        self::assertFileExists($path);

        $contents = (string) file_get_contents($path);

        self::assertStringContainsString("title: 'Vendoring API'", $contents);
        self::assertStringContainsString("description: 'Vendoring API surface'", $contents);
        self::assertStringContainsString("version: '1.0.0-rc'", $contents);
        self::assertStringContainsString("path_patterns: ['^/api(?!/doc$)']", $contents);
    }
}
