<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir().'/config';

        if (is_dir($configDir.'/packages')) {
            $loader->load($configDir.'/packages/*.yaml', 'glob');
        }

        if (is_file($configDir.'/services.yaml')) {
            $loader->load($configDir.'/services.yaml');
        }

        if (is_file($configDir.'/services_runtime.php')) {
            $loader->load($configDir.'/services_runtime.php');
        }

        if (is_file($configDir.'/packages_runtime.php')) {
            $loader->load($configDir.'/packages_runtime.php');
        }
    }

    protected function configureRoutes($routes): void
    {
        $configDir = $this->getProjectDir().'/config';

        if (is_file($configDir.'/routes.yaml')) {
            $routes->import($configDir.'/routes.yaml');
        }

        if (is_file($configDir.'/routes_runtime.php')) {
            $routes->import($configDir.'/routes_runtime.php');
        }
    }
}
