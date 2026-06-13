<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ReleaseCandidateRuntimeActivationContractTest extends TestCase
{
    public function testNativeSurfaceDoesNotRequireLegacyRuntimeActivationFiles(): void
    {
        $projectRoot = dirname(__DIR__, 3);

        self::assertFileDoesNotExist($projectRoot . '/config/packages_runtime.php');
        self::assertFileDoesNotExist($projectRoot . '/config/services_runtime.php');
        self::assertFileDoesNotExist($projectRoot . '/config/vendor_services.yaml');
    }

    public function testKernelDoesNotLoadLegacyRuntimeActivationFiles(): void
    {
        $kernelPath = dirname(__DIR__, 3) . '/src/Kernel.php';
        self::assertFileExists($kernelPath);

        $contents = (string) file_get_contents($kernelPath);

        self::assertStringNotContainsString('packages_runtime.php', $contents);
        self::assertStringNotContainsString('services_runtime.php', $contents);
        self::assertStringNotContainsString('vendor_services.yaml', $contents);
    }
}
