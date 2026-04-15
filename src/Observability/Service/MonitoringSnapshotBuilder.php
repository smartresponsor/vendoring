<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * File-backed monitoring snapshot builder for runtime operators.
 *
 * This builder reads exported NDJSON streams and circuit-breaker state files to produce
 * a deterministic overview of recent runtime health.
 */
final readonly class MonitoringSnapshotBuilder implements MonitoringSnapshotBuilderInterface
{
    /**
     * @var array<string, string>
     */
    private const array PROBE_ARTIFACTS = [
        'transaction' => 'docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md',
        'finance' => 'docs/PHASE61_FINANCE_SYNTHETIC_PROBE.md',
        'payout' => 'docs/PHASE62_PAYOUT_PROCESSING_SYNTHETIC_PROBE.md',
        'postDeploy' => 'docs/PHASE60_DEPLOY_READINESS_POST_DEPLOY_PACK.md',
    ];

    /**
     * @var list<array{0:string,1:bool}>
     */
    private const array TIMESTAMP_FORMATS = [
        [DATE_ATOM, false],
        [DATE_RFC3339_EXTENDED, false],
        ['Y-m-d\TH:i:s.uP', false],
        ['Y-m-d\TH:i:s\Z', true],
        ['Y-m-d\TH:i:s.u\Z', true],
        ['Y-m-d\TH:i:s.v\Z', true],
    ];

    /**
     * Signed numeric epoch formats:
     * - integer: 1713000000, +1713000000, -1713000000
     * - decimal: 1713000000.625
     * - scientific: 1.713E+09
     */
    private const string NUMERIC_TIMESTAMP_PATTERN = '/^[+-]?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?$/';

    public function __construct(
        private string $observabilityDir,
        private string $faultToleranceDir,
        private string $projectDir,
    ) {}

    public function build(int $windowSeconds = 900): array
    {
        $now = time();
        $normalizedWindowSeconds = max(1, $windowSeconds);
        $cutoff = $now - $normalizedWindowSeconds;

        $logSummary = $this->readLogs($cutoff, $now);
        $metricSummary = $this->readMetrics($cutoff, $now);
        $breakerSummary = $this->readBreakers();
        $probeSummary = $this->probeArtifacts();

        $status = 'ok';
        if ($logSummary['error'] > 0 || $logSummary['warning'] > 0 || $breakerSummary['open'] > 0 || in_array(false, $probeSummary, true)) {
            $status = 'warn';
        }

        $generatedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return [
            'generatedAt' => $generatedAt->format(DATE_ATOM),
            'windowSeconds' => $normalizedWindowSeconds,
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
    private function readLogs(int $cutoff, int $now): array
    {
        $path = rtrim($this->observabilityDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'runtime_logs.ndjson';
        $total = 0;
        $error = 0;
        $warning = 0;
        $routes = [];
        $errorCodes = [];

        foreach ($this->readJsonLines($path) as $record) {
            if (!$this->isRecentRecord($record, $cutoff, $now)) {
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

        $routeList = array_values(array_keys($routes));
        sort($routeList, SORT_STRING);

        $errorCodeList = array_values(array_keys($errorCodes));
        sort($errorCodeList, SORT_STRING);

        return [
            'total' => $total,
            'error' => $error,
            'warning' => $warning,
            'routes' => $routeList,
            'errorCodes' => $errorCodeList,
        ];
    }

    /**
     * @return array{total:int,names:array<string,int>}
     */
    private function readMetrics(int $cutoff, int $now): array
    {
        $path = rtrim($this->observabilityDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'runtime_metrics.ndjson';
        $total = 0;
        $names = [];

        foreach ($this->readJsonLines($path) as $record) {
            if (!$this->isRecentRecord($record, $cutoff, $now)) {
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

        $breakerFiles = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
        sort($breakerFiles, SORT_STRING);

        foreach ($breakerFiles as $path) {
            $rawPayload = file_get_contents($path);
            if (!is_string($rawPayload)) {
                continue;
            }

            $payload = json_decode($rawPayload, true);
            if (!is_array($payload)) {
                continue;
            }
            $state = (string) ($payload['state'] ?? 'closed');
            $scopeKey = (string) ($payload['scopeKey'] ?? basename($path));
            if ('open' === $state) {
                ++$open;
                $scopes[] = $scopeKey;
            } elseif ('half_open' === $state) {
                ++$halfOpen;
            } else {
                ++$closed;
            }
        }

        sort($scopes, SORT_STRING);

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
        $probeSummary = [
            'transaction' => false,
            'finance' => false,
            'payout' => false,
            'postDeploy' => false,
        ];

        foreach (self::PROBE_ARTIFACTS as $probeKey => $relativePath) {
            $probeSummary[$probeKey] = is_file(rtrim($this->projectDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath);
        }

        return $probeSummary;
    }

    /**
     * @return \Generator<int, array<string,mixed>, void, void>
     */
    private function readJsonLines(string $path): \Generator
    {
        if (!is_file($path)) {
            return;
        }

        $handle = fopen($path, 'rb');
        if (false === $handle) {
            return;
        }

        try {
            $isFirstLine = true;
            while (($line = fgets($handle)) !== false) {
                if ($isFirstLine) {
                    $line = preg_replace('/^\xEF\xBB\xBF/u', '', $line) ?? $line;
                    $isFirstLine = false;
                }
                $line = trim($line);
                if ('' === $line) {
                    continue;
                }

                $decoded = json_decode($line, true);
                if (is_array($decoded)) {
                    yield $decoded;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param array<string,mixed> $record
     */
    private function isRecentRecord(array $record, int $cutoff, int $now): bool
    {
        $timestamp = $record['timestamp'] ?? null;
        if (is_int($timestamp) || is_float($timestamp)) {
            $unix = (int) $timestamp;
        } elseif (is_string($timestamp)) {
            $timestamp = trim($timestamp);
            if ('' === $timestamp) {
                return false;
            }

            $unix = $this->parseNumericTimestamp($timestamp) ?? $this->parseTimestamp($timestamp);
        } else {
            return false;
        }

        if (null === $unix) {
            return false;
        }
        if ($unix < 0) {
            return false;
        }

        return $unix >= $cutoff && $unix <= $now;
    }

    private function parseNumericTimestamp(string $timestamp): ?int
    {
        if (preg_match(self::NUMERIC_TIMESTAMP_PATTERN, $timestamp) !== 1) {
            return null;
        }

        if (preg_match('/^[+-]?\d+$/', $timestamp) === 1) {
            $normalizedInteger = str_starts_with($timestamp, '+') ? substr($timestamp, 1) : $timestamp;
            $validatedInteger = filter_var($normalizedInteger, FILTER_VALIDATE_INT);
            if (false === $validatedInteger) {
                return null;
            }

            return (int) $validatedInteger;
        }

        $numericTimestamp = (float) $timestamp;
        if (!is_finite($numericTimestamp)) {
            return null;
        }
        if ($numericTimestamp > PHP_INT_MAX || $numericTimestamp < PHP_INT_MIN) {
            return null;
        }

        return (int) $numericTimestamp;
    }

    private function parseTimestamp(string $timestamp): ?int
    {
        $utc = new DateTimeZone('UTC');
        foreach (self::TIMESTAMP_FORMATS as [$format, $useUtcTimezone]) {
            $timezone = $useUtcTimezone ? $utc : null;
            $parsed = DateTimeImmutable::createFromFormat($format, $timestamp, $timezone);
            if (false === $parsed) {
                continue;
            }

            $errors = DateTimeImmutable::getLastErrors();
            if (false !== $errors && (0 !== $errors['warning_count'] || 0 !== $errors['error_count'])) {
                continue;
            }

            return $parsed->getTimestamp();
        }

        return null;
    }
}
