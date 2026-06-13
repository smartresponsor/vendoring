<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ServiceWiringContractTest extends TestCase
{
    public function testCanonicalComponentServicesFileExists(): void
    {
        $path = dirname(__DIR__, 3) . '/config/component/services.yaml';
        self::assertFileExists($path);
    }

    public function testCanonicalComponentMetadataPointsAtServicesFile(): void
    {
        $path = dirname(__DIR__, 3) . '/config/component/component.yaml';
        self::assertFileExists($path);

        $contents = (string) file_get_contents($path);

        self::assertStringContainsString('services: config/component/services.yaml', $contents);
        self::assertStringContainsString('services_loaded_by_extension: config/component/services.yaml', $contents);
    }

    public function testLegacyVendorCoreServicesBridgeIsNotRequiredByNativeSurface(): void
    {
        $path = dirname(__DIR__, 3) . '/config/vendor_services.yaml';
        self::assertFileDoesNotExist($path);
    }

    public function testServiceSurfaceRemainsBundleOwned(): void
    {
        $extensionPath = dirname(__DIR__, 3) . '/src/DependencyInjection/VendoringExtension.php';
        self::assertFileExists($extensionPath);

        $contents = (string) file_get_contents($extensionPath);

        self::assertStringContainsString('component/services', $contents);
    }
}
