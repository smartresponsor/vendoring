<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoLegacyVendorScriptContractTest extends TestCase
{
    public function testReorganizeTestsScriptDoesNotKeepLegacyVendorSegment(): void
    {
        $path = dirname(__DIR__, 3).'/.commanding/reorganize-tests.ps1';
        if (!is_file($path)) {
            self::assertTrue(true);

            return;
        }

        $content = (string) file_get_contents($path);
        self::assertStringNotContainsString("'Vendor')", $content);
        self::assertStringNotContainsString("'Vendor',", $content);
        self::assertStringNotContainsString("keepSubdirs = @('Api','DTO','E2E','Form','Twig','Vendor')", $content);
        self::assertStringNotContainsString('\\Vendor\\VendorEnTest.php', $content);
    }
}
