<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Config;

use App\Vendoring\Service\Config\VendoringFeatureFlagsConfigService;
use App\Vendoring\Value\Form\Config\VendoringFeatureFlagsConfigData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class VendoringFeatureFlagsConfigServiceTest extends TestCase
{
    private string $root;
    private string $path;
    private string $original;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/vendoring-config-'.bin2hex(random_bytes(12));
        $this->path = $this->root.'/config/component/vendor_runtime.yaml';
        $this->original = Yaml::dump([
            'vendoring_feature_flags' => ['old' => ['enabled' => false]],
            'vendoring_alert_thresholds' => ['errorLogThreshold' => 17],
            'vendoring_rollback_thresholds' => ['criticalAlertCodes' => ['custom']],
            'unrelated' => ['keep' => true],
        ]);
        (new Filesystem())->dumpFile($this->path, $this->original);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->root);
    }

    public function testApplyPreservesUnrelatedRuntimePolicyAndBackup(): void
    {
        $service = new VendoringFeatureFlagsConfigService($this->root);
        $loaded = $service->loadData();
        self::assertInstanceOf(VendoringFeatureFlagsConfigData::class, $loaded);
        self::assertSame(['old' => ['enabled' => false]], json_decode($loaded->featureFlagsJson, true, flags: JSON_THROW_ON_ERROR));
        $loaded->featureFlagsJson = '{"canary":{"enabled":false,"cohorts":["tenant:tenant-a","vendor:42"]}}';
        self::assertSame('pending', $service->save($loaded)['status']);
        self::assertSame($this->original, file_get_contents($this->path));
        $result = $service->apply($loaded);
        self::assertSame('applied', $result['status']);
        self::assertSame($this->path, $result['path']);
        self::assertSame($this->original, file_get_contents($this->path.'.bak'));
        $manifest = Yaml::parseFile($this->path);
        self::assertIsArray($manifest);
        self::assertSame(['errorLogThreshold' => 17], $manifest['vendoring_alert_thresholds']);
        self::assertSame(['criticalAlertCodes' => ['custom']], $manifest['vendoring_rollback_thresholds']);
        self::assertSame(['keep' => true], $manifest['unrelated']);
        self::assertSame(['canary' => ['enabled' => false, 'cohorts' => ['tenant:tenant-a', 'vendor:42']]], $manifest['vendoring_feature_flags']);
    }

    public function testInvalidFlagsFailWithoutChangingManifest(): void
    {
        $service = new VendoringFeatureFlagsConfigService($this->root);
        $data = new VendoringFeatureFlagsConfigData();
        $data->featureFlagsJson = '{"bad":{"enabled":"true"}}';
        try {
            $service->apply($data);
            self::fail('Invalid flag must not be written.');
        } catch (\InvalidArgumentException) {
            self::assertSame($this->original, file_get_contents($this->path));
            self::assertFileDoesNotExist($this->path.'.bak');
        }
    }
}
