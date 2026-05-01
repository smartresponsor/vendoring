<?php

declare(strict_types=1);

namespace App\Vendoring\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines the external configuration surface for the Vendoring bundle.
 */
final class VendorConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('vendoring');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('observability_dir')
                    ->defaultValue('%kernel.project_dir%/var/observability')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('fault_tolerance_dir')
                    ->defaultValue('%kernel.project_dir%/var/fault-tolerance')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
