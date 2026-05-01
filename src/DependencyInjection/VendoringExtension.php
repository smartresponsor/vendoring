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
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new VendorConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        /** @var string $observabilityDir */
        $observabilityDir = $config['observability_dir'];

        /** @var string $faultToleranceDir */
        $faultToleranceDir = $config['fault_tolerance_dir'];

        $container->setParameter('vendoring_observability_dir', $observabilityDir);
        $container->setParameter('vendoring_fault_tolerance_dir', $faultToleranceDir);

        $configDirectory = __DIR__ . '/../../config';
        $servicesFile = $configDirectory . '/component/services.yaml';

        if (!is_file($servicesFile)) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator($configDirectory));
        $loader->load('component/services.yaml');
    }

    public function getAlias(): string
    {
        return 'vendoring';
    }

    /**
     * @param array<string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new VendorConfiguration();
    }
}
