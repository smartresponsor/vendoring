<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ops;

use App\Service\Ops\ReleaseManifestBuilder;
use App\ServiceInterface\Observability\AlertRuleEvaluatorInterface;
use App\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use PHPUnit\Framework\TestCase;

final class ReleaseManifestBuilderTest extends TestCase
{
    public function testBuildAggregatesMonitoringAndReleaseArtifacts(): void
    {
        $projectDir = sys_get_temp_dir() . '/vendoring-release-manifest-' . bin2hex(random_bytes(4));
        mkdir($projectDir . '/docs/release', 0777, true);
        mkdir($projectDir . '/build/release', 0777, true);
        mkdir($projectDir . '/build/docs/phpdocumentor', 0777, true);

        foreach (['RC_BASELINE.md', 'RC_RUNTIME_SURFACES.md', 'RC_OPERATOR_SURFACE.md', 'RC_EVIDENCE_PACK.md', 'RC_ROLLBACK_MANIFEST.md', 'RC_RELEASE_MANIFEST.md'] as $file) {
            file_put_contents($projectDir . '/docs/release/' . $file, '# ok');
        }
        foreach (['rc-evidence.json', 'rc-evidence.md', 'release-manifest.json', 'rollback-manifest.json'] as $file) {
            file_put_contents($projectDir . '/build/release/' . $file, '{}');
        }
        file_put_contents($projectDir . '/build/docs/phpdocumentor/index.html', '<html></html>');

        $snapshotBuilder = new class implements MonitoringSnapshotBuilderInterface {
            public function build(int $windowSeconds = 900): array
            {
                return [
                    'status' => 'warn',
                    'breakerSummary' => ['open' => 1],
                    'probeSummary' => ['transaction' => true, 'finance' => true, 'payout' => false, 'postDeploy' => true],
                ];
            }
        };
        $alertEvaluator = new class implements AlertRuleEvaluatorInterface {
            public function evaluate(array $snapshot): array
            {
                return [['code' => 'outbound_circuit_open']];
            }
        };

        $builder = new ReleaseManifestBuilder($snapshotBuilder, $alertEvaluator, $projectDir);
        $manifest = $builder->build(600);

        self::assertSame('warn', $manifest['status']);
        self::assertSame(1, $manifest['monitoring']['alertCount']);
        self::assertSame(['outbound_circuit_open'], $manifest['monitoring']['alertCodes']);
        self::assertSame(1, $manifest['monitoring']['openBreakers']);
        self::assertSame(['payout'], $manifest['monitoring']['missingProbes']);
        self::assertTrue($manifest['releaseDocs']['rcReleaseManifest']);
        self::assertTrue($manifest['buildArtifacts']['releaseManifestJson']);
    }
}
