<?php

declare(strict_types=1);

namespace App\Vendoring\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads the Symfony-native service export for the Vendoring RC component.
 */
final class VendoringExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $templateDir = realpath(__DIR__.'/../../templates');
        if (false === $templateDir) {
            return;
        }

        $container->prependExtensionConfig('twig', [
            'paths' => [
                $templateDir => null,
            ],
        ]);
    }

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

        $runtimeFile = __DIR__.'/../../config/component/runtime.yaml';
        if (is_file($runtimeFile)) {
            $runtime = Yaml::parseFile($runtimeFile);
            if (is_array($runtime)) {
                if (array_key_exists('vendoring_feature_flags', $runtime) && is_array($runtime['vendoring_feature_flags'])) {
                    $container->setParameter('vendoring_feature_flags', $runtime['vendoring_feature_flags']);
                }
                if (array_key_exists('vendoring_alert_thresholds', $runtime) && is_array($runtime['vendoring_alert_thresholds'])) {
                    $container->setParameter('vendoring_alert_thresholds', $runtime['vendoring_alert_thresholds']);
                }
                if (array_key_exists('vendoring_rollback_thresholds', $runtime) && is_array($runtime['vendoring_rollback_thresholds'])) {
                    $container->setParameter('vendoring_rollback_thresholds', $runtime['vendoring_rollback_thresholds']);
                }
            }
        }

        $configDirectory = __DIR__.'/../../config';
        $servicesFile = $configDirectory.'/component/services.yaml';

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
