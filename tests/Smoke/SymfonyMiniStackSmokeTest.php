<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Smoke;

use PHPUnit\Framework\TestCase;

final class SymfonyMiniStackSmokeTest extends TestCase
{
    public function testSymfonyMiniStackFilesExist(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileExists($root . '/config/bundles.php');
        self::assertFileExists($root . '/config/packages/framework.yaml');
        self::assertFileExists($root . '/config/packages/doctrine.yaml');
        self::assertFileExists($root . '/config/packages/vendoring.yaml');
        self::assertFileExists($root . '/config/component/services.yaml');
        self::assertFileExists($root . '/src/VendoringBundle.php');
        self::assertFileExists($root . '/src/DependencyInjection/VendoringExtension.php');
        self::assertFileExists($root . '/src/DependencyInjection/VendorConfiguration.php');
    }
}
