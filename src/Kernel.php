<?php

declare(strict_types=1);

namespace App\Vendoring;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        if (!is_array($contents)) {
            return;
        }

        foreach ($contents as $class => $envs) {
            if (!is_string($class) || !is_array($envs)) {
                continue;
            }

            if (($envs[$this->environment] ?? false) || ($envs['all'] ?? false)) {
                $bundle = new $class();
                if ($bundle instanceof BundleInterface) {
                    yield $bundle;
                }
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $configDir = $this->getProjectDir().'/config';

        $loader->load($configDir.'/packages/*.yaml', 'glob');
        $loader->load($configDir.'/packages/'.$this->environment.'/*.yaml', 'glob');
        $loader->load($configDir.'/services.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getProjectDir().'/config';

        $routes->import($configDir.'/platform/routes*.yaml');
        $routes->import($configDir.'/platform/routes/**/*.yaml');
    }
}
