<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    if (!class_exists(NelmioApiDocBundle::class)) {
        return;
    }

    $routes->import(__DIR__.'/routes/nelmio_api_doc.yaml');
};
