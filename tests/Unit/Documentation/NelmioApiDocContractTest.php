<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class NelmioApiDocContractTest extends TestCase
{
    public function testNelmioApiDocPackageConfigDefinesReleaseCandidateSurface(): void
    {
        $config = (string) file_get_contents(dirname(__DIR__, 3) . '/config/packages/nelmio_api_doc.yaml');

        self::assertStringContainsString('nelmio_api_doc:', $config);
        self::assertStringContainsString("title: 'Vendoring API'", $config);
        self::assertStringContainsString("description: 'Release-candidate API documentation surface for the vendoring component.'", $config);
        self::assertStringContainsString("version: '1.0.0-rc'", $config);
        self::assertStringContainsString('^/api(?!/doc$)', $config);
        self::assertStringContainsString('^/api(?!/doc\\.json$)', $config);
    }

    public function testRuntimeRoutingImportsNelmioSwaggerUiSurface(): void
    {
        $routesRuntime = (string) file_get_contents(dirname(__DIR__, 3) . '/config/routes_runtime.php');
        $routes = (string) file_get_contents(dirname(__DIR__, 3) . '/config/routes/vendor_nelmio_api_doc.yaml');

        self::assertStringContainsString("routes->import(__DIR__.'/routes/vendor_nelmio_api_doc.yaml');", $routesRuntime);
        self::assertStringContainsString("resource: '@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml'", $routes);
        self::assertStringContainsString('prefix: /api/doc', $routes);
    }
}
