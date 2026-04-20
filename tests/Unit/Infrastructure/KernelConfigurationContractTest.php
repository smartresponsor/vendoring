<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class KernelConfigurationContractTest extends TestCase
{
    public function testLegacyHostBridgeImportsCanonicalComponentServices(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_services.yaml');

        self::assertStringContainsString("resource: 'component/services.yaml'", $services);
        self::assertStringContainsString('canonical reusable-bundle surface', $services);
    }

    public function testCanonicalComponentServicesExportOwnsAppResourceAndTransactionsBridge(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3) . '/config/component/services.yaml');

        self::assertStringContainsString("resource: '../vendor_services_transactions.yaml'", $services);
        self::assertStringContainsString('App\\Vendoring\\:', $services);
        self::assertStringContainsString('../../src/Entity/', $services);
        self::assertStringContainsString("resource: '../../src/Controller/'", $services);
    }

    public function testRoutesYamlImportsControllerAttributesAndVendorTransactionsRoutes(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_routes.yaml');

        self::assertStringContainsString('../src/Controller/', $routes);
        self::assertStringContainsString('type: attribute', $routes);
        self::assertStringNotContainsString('routes_vendor_transactions.yaml', $routes);
    }

    public function testDoctrineYamlMapsAppEntityNamespace(): void
    {
        $doctrine = (string) file_get_contents(dirname(__DIR__, 3) . '/config/packages/doctrine.yaml');

        self::assertStringContainsString("prefix: 'App\Vendoring\\Entity'", $doctrine);
        self::assertStringContainsString("dir: '%kernel.project_dir%/src/Entity'", $doctrine);
        self::assertStringContainsString('type: attribute', $doctrine);
    }
}
