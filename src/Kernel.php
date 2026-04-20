<?php

declare(strict_types=1);

namespace App\Vendoring;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getProjectDir() . '/config';

        if (is_dir($configDir . '/packages')) {
            $container->import($configDir . '/packages/*.yaml');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir() . '/config';

        if (is_file($configDir . '/vendor_routes.yaml')) {
            $routes->import($configDir . '/vendor_routes.yaml');
        }
    }
}
