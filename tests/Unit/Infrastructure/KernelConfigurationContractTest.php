<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class KernelConfigurationContractTest extends TestCase
{
    public function testKernelLoadsOnlyNativePackageSurface(): void
    {
        $kernelPath = dirname(__DIR__, 3) . '/src/Kernel.php';
        self::assertFileExists($kernelPath);

        $contents = (string) file_get_contents($kernelPath);

        self::assertStringContainsString("\$container->import(\$configDir . '/packages/*.yaml');", $contents);
        self::assertStringNotContainsString('packages_runtime.php', $contents);
        self::assertStringNotContainsString('services_runtime.php', $contents);
        self::assertStringNotContainsString('vendor_services.yaml', $contents);
    }

    public function testVendoringExtensionLoadsCanonicalComponentServices(): void
    {
        $extensionPath = dirname(__DIR__, 3) . '/src/DependencyInjection/VendoringExtension.php';
        self::assertFileExists($extensionPath);

        $contents = (string) file_get_contents($extensionPath);

        self::assertStringContainsString('component/services', $contents);
    }
}
