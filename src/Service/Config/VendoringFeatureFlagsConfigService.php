<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Config;

use App\Vendoring\Form\Config\VendoringFeatureFlagsConfigFormType;
use App\Vendoring\Value\Form\Config\VendoringFeatureFlagsConfigData;
use Symfony\Component\Yaml\Yaml;

final readonly class VendoringFeatureFlagsConfigService
{
    public function __construct(private string $projectDir)
    {
    }

    /**
     * @return array{
     *   applicationCode:string,
     *   toolCode:string,
     *   label:string,
     *   description:string,
     *   formClass:class-string,
     *   serviceClass:class-string,
     *   requiredPermission:string,
     *   editableFields:list<string>,
     *   sensitiveFields:list<string>,
     *   readableFiles:list<string>,
     *   writableFiles:list<string>,
     *   metadata:array{section:string, kind:string},
     *   secretNames:list<string>,
     *   applyStrategy:string
     * }
     */
    public function descriptor(): array
    {
        return [
            'applicationCode' => 'Vendoring',
            'toolCode' => 'vendoring.feature_flags',
            'label' => 'Vendoring Feature Flags',
            'description' => 'Safe runtime feature flags stored in vendoring runtime manifest.',
            'formClass' => VendoringFeatureFlagsConfigFormType::class,
            'serviceClass' => self::class,
            'requiredPermission' => 'administration.config.update',
            'editableFields' => ['featureFlagsJson'],
            'sensitiveFields' => [],
            'readableFiles' => ['config/component/vendor_runtime.yaml'],
            'writableFiles' => ['config/component/vendor_runtime.yaml'],
            'metadata' => [
                'section' => 'Configuration',
                'kind' => 'feature_flags',
            ],
            'secretNames' => [],
            'applyStrategy' => 'component_runtime_yaml',
        ];
    }

    public function loadData(): object
    {
        $data = new VendoringFeatureFlagsConfigData();
        $manifest = $this->runtimeManifest();
        $data->featureFlagsJson = json_encode($manifest['vendoring_feature_flags'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{status:string, actor:string, values:array<string, array{fieldType:string, secret:bool, current:?string, pending:?string, masked:?string, status:string}>}
     */
    public function save(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $actor = $context['actor'] ?? 'system';

        return [
            'status' => 'pending',
            'actor' => is_string($actor) ? $actor : 'system',
            'values' => $this->stateRows($payload, 'pending'),
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{status:string, actor:string, path:string, backup_path:?string, message:string, values:array<string, array{fieldType:string, secret:bool, current:?string, pending:?string, masked:?string, status:string}>}
     */
    public function apply(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $patch = $this->runtimePatch($payload);
        $path = $this->projectDir.'/config/component/vendor_runtime.yaml';
        $backupPath = is_file($path) ? $path.'.bak' : null;

        if (null !== $backupPath && !copy($path, $backupPath)) {
            throw new \RuntimeException('Unable to back up Vendoring runtime manifest.');
        }

        $encoded = Yaml::dump($patch, 4, 2);
        if (false === file_put_contents($path, $encoded, LOCK_EX)) {
            throw new \RuntimeException('Unable to write Vendoring runtime manifest.');
        }

        $actor = $context['actor'] ?? 'system';

        return [
            'status' => 'applied',
            'actor' => is_string($actor) ? $actor : 'system',
            'path' => $path,
            'backup_path' => $backupPath,
            'message' => 'Vendoring runtime feature flags applied.',
            'values' => $this->stateRows($payload, 'applied'),
        ];
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
        $path = $this->projectDir.'/../Vendoring/config/component/vendor_runtime.yaml';
        $parsed = is_file($path) ? Yaml::parseFile($path) : [];
        if (!is_array($parsed)) {
            return [];
        }

        $manifest = [];
        foreach ($parsed as $key => $value) {
            if (is_string($key)) {
                $manifest[$key] = $value;
            }
        }

        return $manifest;
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
