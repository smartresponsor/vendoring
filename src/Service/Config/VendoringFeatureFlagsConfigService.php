<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Config;

use App\Vendoring\Form\Config\VendoringFeatureFlagsConfigFormType;
use App\Vendoring\Value\Form\Config\VendoringFeatureFlagsConfigData;
use Symfony\Component\Filesystem\Filesystem;
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
        $this->validatedFlags($payload);
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
        $flags = $this->validatedFlags($payload);
        $path = $this->manifestPath();
        if (is_link($path.'.lock')) {
            throw new \RuntimeException('Refusing symlinked manifest lock.');
        }
        $lock = fopen($path.'.lock', 'c');
        if (false === $lock) {
            throw new \RuntimeException('Unable to lock Vendoring runtime manifest.');
        }
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Unable to lock Vendoring runtime manifest.');
            }
            $manifest = $this->runtimeManifest();
            $manifest['vendoring_feature_flags'] = $flags;
            $backupPath = $path.'.bak';
            $filesystem = new Filesystem();
            if (is_link($backupPath)) {
                throw new \RuntimeException('Refusing symlinked manifest state.');
            }
            $original = file_get_contents($path);
            if (false === $original) {
                throw new \RuntimeException('Unable to read manifest for backup.');
            }
            $filesystem->dumpFile($backupPath, $original);
            $filesystem->dumpFile($path, Yaml::dump($manifest, 8, 2));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
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
        $parsed = Yaml::parseFile($this->manifestPath());
        if (!is_array($parsed)) {
            throw new \RuntimeException('Invalid Vendoring runtime manifest.');
        }

        $manifest = [];
        foreach ($parsed as $key => $value) {
            if (!is_string($key)) {
                throw new \RuntimeException('Runtime manifest must contain named keys.');
            }
            $manifest[$key] = $value;
        }

        return $manifest;
    }

    private function manifestPath(): string
    {
        $path = $this->projectDir.'/config/component/vendor_runtime.yaml';
        if (!is_file($path) || is_link($path)) {
            throw new \RuntimeException('Vendoring runtime manifest is missing or is not a regular file.');
        }

        return $path;
    }

    /** @return array<string, array{enabled?:bool, cohorts?:list<string>}> */
    private function validatedFlags(VendoringFeatureFlagsConfigData $data): array
    {
        $decoded = json_decode($data->featureFlagsJson, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ([] !== $decoded && array_is_list($decoded))) {
            throw new \InvalidArgumentException('Feature flags must be a name-keyed object.');
        }
        $flags = [];
        foreach ($decoded as $name => $flag) {
            if (!is_string($name) || '' === trim($name) || !is_array($flag) || [] !== array_diff(array_keys($flag), ['enabled', 'cohorts'])) {
                throw new \InvalidArgumentException('Invalid feature flag definition.');
            }
            $definition = [];
            if (array_key_exists('enabled', $flag)) {
                if (!is_bool($flag['enabled'])) {
                    throw new \InvalidArgumentException('Feature flag enabled must be boolean.');
                }
                $definition['enabled'] = $flag['enabled'];
            }
            if (array_key_exists('cohorts', $flag)) {
                if (!is_array($flag['cohorts']) || !array_is_list($flag['cohorts'])) {
                    throw new \InvalidArgumentException('Feature flag cohorts must be a list.');
                }
                $cohorts = [];
                foreach ($flag['cohorts'] as $cohort) {
                    if (!is_string($cohort) || 1 !== preg_match('/^(?:global|vendor:[1-9][0-9]*|tenant:[^\s:]+)$/D', $cohort)) {
                        throw new \InvalidArgumentException('Invalid feature flag cohort.');
                    }
                    $cohorts[] = $cohort;
                }
                $definition['cohorts'] = $cohorts;
            }
            $flags[$name] = $definition;
        }

        return $flags;
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
