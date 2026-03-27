<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\TestCase;

final class SymfonyMiniStackSmokeTest extends TestCase
{
    public function testSymfonyMiniStackFilesExist(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileExists($root.'/src/Kernel.php');
        self::assertFileExists($root.'/config/bundles.php');
        self::assertFileExists($root.'/config/packages/framework.yaml');
        self::assertFileExists($root.'/config/packages/doctrine.yaml');
        self::assertFileExists($root.'/config/vendor_services.yaml');
        self::assertFileExists($root.'/config/vendor_routes.yaml');
        self::assertFileExists($root.'/bin/console');
        self::assertFileExists($root.'/public/index.php');
    }

    public function testBundlesDeclareFrameworkAndDoctrine(): void
    {
        $bundles = require dirname(__DIR__, 2).'/config/bundles.php';

        self::assertArrayHasKey(\Symfony\Bundle\FrameworkBundle\FrameworkBundle::class, $bundles);
        self::assertArrayHasKey(\Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class, $bundles);
        self::assertSame(['all' => true], $bundles[\Symfony\Bundle\FrameworkBundle\FrameworkBundle::class]);
        self::assertSame(['all' => true], $bundles[\Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class]);
    }
}
