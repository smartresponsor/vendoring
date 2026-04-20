<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\DependencyInjection;

use App\Vendoring\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class VendoringConfigurationTest extends TestCase
{
    public function testConfigurationProvidesCanonicalReusableBundleDefaults(): void
    {
        $processor = new Processor();

        /** @var array<string, mixed> $config */
        $config = $processor->processConfiguration(new Configuration(), [[]]);

        self::assertSame('%kernel.secret%', $config['secret']);
        self::assertSame('%kernel.project_dir%/var/observability', $config['observability_dir']);
        self::assertSame('%kernel.project_dir%/var/fault-tolerance', $config['fault_tolerance_dir']);
        self::assertSame([], $config['feature_flags']);
        self::assertSame(1, $config['alert_thresholds']['errorLogThreshold']);
        self::assertSame(['outbound_circuit_open'], $config['rollback_thresholds']['criticalAlertCodes']);
    }

    public function testConfigurationAcceptsExternalHostOverrides(): void
    {
        $processor = new Processor();

        /** @var array<string, mixed> $config */
        $config = $processor->processConfiguration(new Configuration(), [[
            'secret' => 'custom-secret',
            'observability_dir' => '/tmp/obs',
            'fault_tolerance_dir' => '/tmp/ft',
            'feature_flags' => [
                'vendor_portal' => [
                    'enabled' => true,
                    'cohorts' => ['beta'],
                ],
            ],
            'alert_thresholds' => [
                'openBreakerThreshold' => 3,
            ],
            'rollback_thresholds' => [
                'warningAlertCodes' => ['probe_artifacts_missing'],
            ],
        ]]);

        self::assertSame('custom-secret', $config['secret']);
        self::assertSame('/tmp/obs', $config['observability_dir']);
        self::assertSame('/tmp/ft', $config['fault_tolerance_dir']);
        self::assertTrue($config['feature_flags']['vendor_portal']['enabled']);
        self::assertSame(['beta'], $config['feature_flags']['vendor_portal']['cohorts']);
        self::assertSame(3, $config['alert_thresholds']['openBreakerThreshold']);
        self::assertSame(['probe_artifacts_missing'], $config['rollback_thresholds']['warningAlertCodes']);
    }
}
