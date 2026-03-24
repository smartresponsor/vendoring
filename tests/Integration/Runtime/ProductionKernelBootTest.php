<?php

declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\Tests\Support\Runtime\KernelRuntimeHarness;
use PHPUnit\Framework\TestCase;

final class ProductionKernelBootTest extends TestCase
{
    public function testProdKernelBootsWithFreshSqliteDatabase(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for production kernel boot test');
        }

        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(
            dirname(__DIR__, 3),
            environment: 'prod',
            debug: false,
        );

        self::assertTrue($kernel->getContainer()->has('router'));
        self::assertTrue($kernel->getContainer()->has('doctrine'));

        $response = KernelRuntimeHarness::requestJson($kernel, 'GET', '/api/vendor-transactions/vendor/vendor-boot');
        $payload = KernelRuntimeHarness::decodeJson($response);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $payload['data']);

        $kernel->shutdown();
    }
}
