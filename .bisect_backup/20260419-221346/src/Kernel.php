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

    public function getCacheDir(): string
    {
        if ('1' === ($_ENV['VENDOR_RUNTIME_HARNESS'] ?? $_SERVER['VENDOR_RUNTIME_HARNESS'] ?? null)) {
            $projectHash = substr(sha1($this->getProjectDir()), 0, 12);
            $processId = (string) getmypid();

            return sys_get_temp_dir() . sprintf('/vendoring_kernel_%s_%s_%s', $this->environment, $projectHash, $processId);
        }

        return parent::getCacheDir();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getProjectDir() . '/config';

        if (is_dir($configDir . '/packages')) {
            $container->import($configDir . '/packages/*.yaml');
        }

        if (is_file($configDir . '/services_runtime.php')) {
            $container->import($configDir . '/services_runtime.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir() . '/config';

        if (is_file($configDir . '/vendor_routes.yaml')) {
            $routes->import($configDir . '/vendor_routes.yaml');
        }

        if (is_file($configDir . '/routes_runtime.php')) {
            $routes->import($configDir . '/routes_runtime.php');
        }
    }
}
