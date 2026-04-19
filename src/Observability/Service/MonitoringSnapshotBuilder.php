<?php

declare(strict_types=1);

namespace App\Vendoring\Observability\Service;

use App\Vendoring\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use DateTimeImmutable;

/**
 * File-backed monitoring snapshot builder for runtime operators.
 *
 * This builder reads exported NDJSON streams and circuit-breaker state files to produce
 * a deterministic overview of recent runtime health.
 */
final readonly class MonitoringSnapshotBuilder implements MonitoringSnapshotBuilderInterface
{
    public function __construct(
        private string $observabilityDir,
        private string $faultToleranceDir,
        private string $projectDir,
    ) {}

    public function build(int $windowSeconds = 900): array
    {
        $now = time();
        $cutoff = $now - max(1, $windowSeconds);

        $logSummary = $this->readLogs($cutoff);
        $metricSummary = $this->readMetrics($cutoff);
        $breakerSummary = $this->readBreakers();
        $probeSummary = $this->probeArtifacts();

        $status = 'ok';
        if ($logSummary['error'] > 0 || $breakerSummary['open'] > 0 || in_array(false, $probeSummary, true)) {
            $status = 'warn';
        }

        $generatedAt = new DateTimeImmutable();

        return [
            'generatedAt' => $generatedAt->format(DATE_ATOM),
            'windowSeconds' => $windowSeconds,
            'logSummary' => $logSummary,
            'metricSummary' => $metricSummary,
            'breakerSummary' => $breakerSummary,
            'probeSummary' => $probeSummary,
            'status' => $status,
        ];
    }

    /**
     * @return array{total:int,error:int,warning:int,routes:list<string>,errorCodes:list<string>}
     */
    private function readLogs(int $cutoff): array
    {
        $path = rtrim($this->observabilityDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'runtime_logs.ndjson';
        $total = 0;
        $error = 0;
        $warning = 0;
        $routes = [];
        $errorCodes = [];

        foreach ($this->readJsonLines($path) as $record) {
            if (!$this->isRecentRecord($record, $cutoff)) {
                continue;
            }
            ++$total;
            $levelValue = $record['level'] ?? null;
            $level = is_string($levelValue) ? $levelValue : '';
            if ('error' === $level) {
                ++$error;
            }
            if ('warning' === $level) {
                ++$warning;
            }
            $route = $record['route'] ?? null;
            if (is_string($route) && '' !== $route) {
                $routes[$route] = true;
            }
            $errorCode = $record['error_code'] ?? null;
            if (is_string($errorCode) && '' !== $errorCode) {
                $errorCodes[$errorCode] = true;
            }
        }

        return [
            'total' => $total,
            'error' => $error,
            'warning' => $warning,
            'routes' => array_keys($routes),
            'errorCodes' => array_keys($errorCodes),
        ];
    }

    /**
     * @return array{total:int,names:array<string,int>}
     */
    private function readMetrics(int $cutoff): array
    {
        $path = rtrim($this->observabilityDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'runtime_metrics.ndjson';
        $total = 0;
        $names = [];

        foreach ($this->readJsonLines($path) as $record) {
            if (!$this->isRecentRecord($record, $cutoff)) {
                continue;
            }
            ++$total;
            $name = $record['name'] ?? null;
            if (is_string($name) && '' !== $name) {
                $names[$name] = ($names[$name] ?? 0) + 1;
            }
        }

        ksort($names);

        return [
            'total' => $total,
            'names' => $names,
        ];
    }

    /**
     * @return array{open:int,halfOpen:int,closed:int,scopes:list<string>}
     */
    private function readBreakers(): array
    {
        $dir = rtrim($this->faultToleranceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'circuit-breakers';
        $open = 0;
        $halfOpen = 0;
        $closed = 0;
        $scopes = [];

        if (!is_dir($dir)) {
            return ['open' => 0, 'halfOpen' => 0, 'closed' => 0, 'scopes' => []];
        }

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $path) {
            $rawPayload = file_get_contents($path);
            if (!is_string($rawPayload)) {
                continue;
            }

            $payload = json_decode($rawPayload, true);
            if (!is_array($payload)) {
                continue;
            }
            $state = $this->scalarStringOrDefault($payload['state'] ?? null, 'closed');
            $scopeKey = $this->scalarStringOrDefault($payload['scopeKey'] ?? null, basename($path));
            if ('open' === $state) {
                ++$open;
                $scopes[] = $scopeKey;
            } elseif ('half_open' === $state) {
                ++$halfOpen;
            } else {
                ++$closed;
            }
        }

        sort($scopes);

        return [
            'open' => $open,
            'halfOpen' => $halfOpen,
            'closed' => $closed,
            'scopes' => $scopes,
        ];
    }

    /**
     * @return array{transaction:bool,finance:bool,payout:bool,postDeploy:bool}
     */
    private function probeArtifacts(): array
    {
        return [
            'transaction' => is_file($this->projectDir . '/docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md'),
            'finance' => is_file($this->projectDir . '/docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md'),
            'payout' => is_file($this->projectDir . '/docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md'),
            'postDeploy' => is_file($this->projectDir . '/docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md'),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function readJsonLines(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return [];
        }

        $records = [];
        foreach ($lines as $line) {
            $decoded = json_decode((string) $line, true);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $decoded */
                $records[] = $decoded;
            }
        }

        return $records;
    }

    /**
     * @param array<string,mixed> $record
     */
    private function isRecentRecord(array $record, int $cutoff): bool
    {
        $timestamp = $record['timestamp'] ?? null;
        if (!is_string($timestamp) || '' === $timestamp) {
            return true;
        }
        $unix = strtotime($timestamp);
        if (false === $unix) {
            return true;
        }

        return $unix >= $cutoff;
    }

    private function scalarStringOrDefault(mixed $value, string $default): string
    {
        return is_scalar($value) ? (string) $value : $default;
    }
}
