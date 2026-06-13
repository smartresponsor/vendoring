<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalAppNamespaceRepositoryContractTest extends TestCase
{
    public function testRepositoryOperationalServiceConfigUsesCanonicalAppNamespace(): void
    {
        $file = __DIR__ . '/../../../ops/policy/config/services_interface.yaml';

        self::assertFileExists($file);

        $content = (string) file_get_contents($file);

        self::assertStringContainsString('App\Vendoring\\:', $content);
        self::assertStringContainsString('App\Vendoring\\ServiceInterface\\Core\\VendorCoreServiceInterface:', $content);
        self::assertStringContainsString('alias: App\Vendoring\\Service\\Core\\VendorCoreService', $content);
        self::assertStringNotContainsString('VendorEntity\\:', $content);
        self::assertStringNotContainsString('VendorEntity\\ServiceInterface\\Core\\VendorCoreServiceInterface:', $content);
        self::assertStringNotContainsString('alias: VendorEntity\\Service\\Core\\VendorCoreService', $content);
    }
}
