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

            if ('fixtures' === $this->environment && !in_array($class, [
                \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
                \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class,
            ], true)) {
                continue;
            }

            if ('fixtures' === $this->environment || ($envs[$this->environment] ?? false) || ($envs['all'] ?? false)) {
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

        if ('fixtures' === $this->environment) {
            $loader->load($configDir.'/packages/framework.yaml');
            $loader->load($configDir.'/packages/doctrine.yaml');
            $loader->load($configDir.'/services.yaml');

            return;
        }

        $loader->load($configDir.'/packages/*.yaml', 'glob');
        $environmentPackagesDir = $configDir.'/packages/'.$this->environment;
        if (is_dir($environmentPackagesDir)) {
            $loader->load($environmentPackagesDir.'/*.yaml', 'glob');
        }
        $loader->load($configDir.'/services.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        if ('fixtures' === $this->environment) {
            return;
        }

        $configDir = $this->getProjectDir().'/config';

        $routes->import($configDir.'/platform/routes*.yaml');
        $routes->import($configDir.'/platform/routes/**/*.yaml');
    }
}
