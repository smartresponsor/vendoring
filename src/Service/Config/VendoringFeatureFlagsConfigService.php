<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Config;

use App\Administering\Service\Config\ConfigApplyService;
use App\Administering\Service\Config\ConfigFileWriterService;
use App\Administering\ServiceInterface\Config\AdministrationConfigToolServiceInterface;
use App\Administering\Value\Config\AdministrationConfigToolDescriptor;
use App\Vendoring\Form\Config\VendoringFeatureFlagsConfigFormType;
use App\Vendoring\Value\Form\Config\VendoringFeatureFlagsConfigData;
use Symfony\Component\Yaml\Yaml;

final readonly class VendoringFeatureFlagsConfigService implements AdministrationConfigToolServiceInterface
{
    public function __construct(
        private string $projectDir,
        private ConfigApplyService $applyService,
        private ConfigFileWriterService $fileWriter,
    ) {
    }

    public function descriptor(): AdministrationConfigToolDescriptor
    {
        return new AdministrationConfigToolDescriptor(
            applicationCode: 'Vendoring',
            toolCode: 'vendoring.feature_flags',
            label: 'Vendoring Feature Flags',
            description: 'Safe runtime feature flags stored in vendoring runtime manifest.',
            formClass: VendoringFeatureFlagsConfigFormType::class,
            serviceClass: self::class,
            requiredPermission: 'administration.config.update',
            editableFields: ['featureFlagsJson'],
            sensitiveFields: [],
            readableFiles: ['config/component/runtime.yaml'],
            writableFiles: ['config/component/runtime.yaml'],
            metadata: [
                'section' => 'Configuration',
                'kind' => 'feature_flags',
            ],
            secretNames: [],
            applyStrategy: 'component_runtime_yaml',
        );
    }

    public function loadData(): object
    {
        $data = new VendoringFeatureFlagsConfigData();
        $manifest = $this->runtimeManifest();
        $data->featureFlagsJson = json_encode($manifest['vendoring_feature_flags'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';

        return $data;
    }

    public function save(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $values = $this->stateRows($payload, 'pending');
        $masked = [
            'vendoring_feature_flags' => $payload->featureFlagsJson,
        ];

        return $this->applyService->save($this->descriptor(), (string) ($context['actor'] ?? 'system'), $values, $masked, []);
    }

    public function apply(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $patch = $this->runtimePatch($payload);
        $write = $this->fileWriter->write(
            $this->projectDir.'/../Vendoring',
            'config/component/runtime.yaml',
            $patch,
            $this->descriptor()->writableFiles,
        );

        $status = 'applied' === $write['status'] ? 'applied' : 'failed';
        $values = $this->stateRows($payload, $status);

        return $this->applyService->apply(
            $this->descriptor(),
            (string) ($context['actor'] ?? 'system'),
            $values,
            $patch,
            [],
            [[
                'path' => $write['path'],
                'backup_path' => $write['backup_path'],
                'status' => $write['status'],
                'message' => $write['message'],
            ]],
            [],
            'applied' === $write['status'] ? null : $write['message'],
            $status,
        );
    }

    private function assertData(object $data): VendoringFeatureFlagsConfigData
    {
        if (!$data instanceof VendoringFeatureFlagsConfigData) {
            throw new \InvalidArgumentException('Vendoring feature flags config expects VendoringFeatureFlagsConfigData.');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function runtimeManifest(): array
    {
        $path = $this->projectDir.'/../Vendoring/config/component/runtime.yaml';
        $parsed = is_file($path) ? Yaml::parseFile($path) : [];

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function runtimePatch(VendoringFeatureFlagsConfigData $data): array
    {
        $decoded = json_decode($data->featureFlagsJson, true);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('VENDORING_FEATURE_FLAGS_JSON must be valid JSON object or array.');
        }

        return [
            'vendoring_feature_flags' => $decoded,
            'vendoring_alert_thresholds' => [
                'errorLogThreshold' => 1,
                'openBreakerThreshold' => 1,
                'missingProbeThreshold' => 1,
            ],
            'vendoring_rollback_thresholds' => [
                'criticalAlertCodes' => ['outbound_circuit_open'],
                'warningAlertCodes' => ['runtime_error_spike', 'probe_artifacts_missing', 'observability_metrics_empty'],
            ],
        ];
    }

    /**
     * @return array<string, array{fieldType:string, secret:bool, current:?string, pending:?string, masked:?string, status:string}>
     */
    private function stateRows(VendoringFeatureFlagsConfigData $data, string $status): array
    {
        return [
            'vendoring_feature_flags' => ['fieldType' => 'textarea', 'secret' => false, 'current' => $data->featureFlagsJson, 'pending' => $data->featureFlagsJson, 'masked' => null, 'status' => $status],
        ];
    }
}
