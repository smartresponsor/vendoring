<?php

declare(strict_types=1);

namespace App\Vendoring\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Loads the Symfony-native service export for the Vendoring RC component.
 */
final class VendoringExtension extends Extension
{
    /**
     * @param array<int, array<string, mixed>> $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('vendoring_observability_dir', $config['observability_dir']);
        $container->setParameter('vendoring_fault_tolerance_dir', $config['fault_tolerance_dir']);

        $configDirectory = __DIR__ . '/../../config/component';
        $servicesFile = $configDirectory . '/services.yaml';

        if (!is_file($servicesFile)) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator($configDirectory));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'vendoring';
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new Configuration();
    }
}
