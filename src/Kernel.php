<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getProjectDir().'/config';
        $environment = $this->environment;

        if (is_dir($configDir.'/packages')) {
            $container->import($configDir.'/packages/*.yaml');
        }

        if (is_dir($configDir.'/packages/'.$environment)) {
            $container->import($configDir.'/packages/'.$environment.'/*.yaml');
        }

        if (is_file($configDir.'/vendor_services.yaml')) {
            $container->import($configDir.'/vendor_services.yaml');
        }

        if (is_file($configDir.'/vendor_services_'.$environment.'.yaml')) {
            $container->import($configDir.'/vendor_services_'.$environment.'.yaml');
        }

        if (is_file($configDir.'/services_runtime.php')) {
            $container->import($configDir.'/services_runtime.php');
        }

        if (is_file($configDir.'/packages_runtime.php')) {
            $container->import($configDir.'/packages_runtime.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir().'/config';
        $environment = $this->environment;

        if (is_file($configDir.'/vendor_routes.yaml')) {
            $routes->import($configDir.'/vendor_routes.yaml');
        }

        if (is_dir($configDir.'/routes/'.$environment)) {
            $routes->import($configDir.'/routes/'.$environment.'/*.yaml');
        }

        if (is_file($configDir.'/routes_runtime.php')) {
            $routes->import($configDir.'/routes_runtime.php');
        }
    }
}
