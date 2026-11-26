<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\DependencyInjection\VendoringExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

final class KernelConfigurationContractTest extends TestCase
{
    public function testKernelLoadsOnlyNativePackageSurface(): void
    {
        $kernelPath = dirname(__DIR__, 3).'/src/Kernel.php';
        self::assertFileExists($kernelPath);

        $contents = (string) file_get_contents($kernelPath);

        self::assertStringContainsString("\$loader->load(\$configDir.'/packages/*.yaml', 'glob');", $contents);
        self::assertStringNotContainsString('packages_runtime.php', $contents);
        self::assertStringNotContainsString('services_runtime.php', $contents);
        self::assertStringNotContainsString('vendor_services.yaml', $contents);
    }

    public function testRuntimeManifestAndBundleConfigurationOverrideServiceDefaults(): void
    {
        $container = new ContainerBuilder();
        $root = dirname(__DIR__, 3);
        $container->setParameter('kernel.project_dir', '/isolated-host');
        $container->setParameter('kernel.secret', 'test-only');
        (new VendoringExtension())->load([[
            'observability_dir' => '/isolated-observability',
            'fault_tolerance_dir' => '/isolated-fault-tolerance',
        ]], $container);
        self::assertSame($root, $container->getParameter('vendoring_component_dir'));
        self::assertSame('/isolated-observability', $container->getParameter('vendoring_observability_dir'));
        self::assertSame('/isolated-fault-tolerance', $container->getParameter('vendoring_fault_tolerance_dir'));
        $manifest = Yaml::parseFile($root.'/config/component/vendor_runtime.yaml');
        self::assertIsArray($manifest);
        self::assertSame($manifest['vendoring_alert_thresholds'], $container->getParameter('vendoring_alert_thresholds'));
        self::assertSame($manifest['vendoring_rollback_thresholds'], $container->getParameter('vendoring_rollback_thresholds'));
        self::assertSame($manifest['vendoring_feature_flags'], $container->getParameter('vendoring_feature_flags'));
    }

    public function testVendoringExtensionLoadsCanonicalComponentServices(): void
    {
        $extensionPath = dirname(__DIR__, 3).'/src/DependencyInjection/VendoringExtension.php';
        self::assertFileExists($extensionPath);

        $contents = (string) file_get_contents($extensionPath);

        self::assertStringContainsString('component/services', $contents);
    }
}
