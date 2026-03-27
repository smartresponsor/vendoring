<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if ('1' === ($_ENV['VENDOR_RUNTIME_HARNESS'] ?? $_SERVER['VENDOR_RUNTIME_HARNESS'] ?? null)) {
        return;
    }

    if (!class_exists(NelmioApiDocBundle::class)) {
        return;
    }

    $reflection = new ReflectionClass(NelmioApiDocBundle::class);
    $bundleRoot = dirname($reflection->getFileName(), 2);
    $swaggerUiRoute = $bundleRoot.'/Resources/config/routing/swaggerui.xml';

    if (!is_file($swaggerUiRoute)) {
        return;
    }

    $routes->import(__DIR__.'/routes/vendor_nelmio_api_doc.yaml');
};
