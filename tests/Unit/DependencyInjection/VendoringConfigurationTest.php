<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\DependencyInjection;

use App\Vendoring\DependencyInjection\VendorConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class VendoringConfigurationTest extends TestCase
{
    public function testConfigurationProvidesCanonicalReusableBundleDefaults(): void
    {
        $processor = new Processor();
        $configuration = new VendorConfiguration();

        $config = $processor->processConfiguration($configuration, []);
        /** @var array<string, mixed> $config */
        $config = $config;

        self::assertSame('%kernel.project_dir%/var/observability', $config['observability_dir']);
        self::assertSame('%kernel.project_dir%/var/fault-tolerance', $config['fault_tolerance_dir']);
    }

    public function testConfigurationAcceptsExternalHostOverrides(): void
    {
        $processor = new Processor();
        $configuration = new VendorConfiguration();

        $config = $processor->processConfiguration($configuration, [[
            'observability_dir' => '/tmp/vendoring-observability',
            'fault_tolerance_dir' => '/tmp/vendoring-fault-tolerance',
        ]]);
        /** @var array<string, mixed> $config */
        $config = $config;

        self::assertSame('/tmp/vendoring-observability', $config['observability_dir']);
        self::assertSame('/tmp/vendoring-fault-tolerance', $config['fault_tolerance_dir']);
    }
}
