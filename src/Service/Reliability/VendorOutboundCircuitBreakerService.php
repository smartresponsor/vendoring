<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Reliability;

use App\Vendoring\DTO\Reliability\VendorOutboundCircuitBreakerStateDTO;
use App\Vendoring\ServiceInterface\Reliability\VendorOutboundCircuitBreakerServiceInterface;

/**
 * File-backed circuit-breaker with atomic state writes.
 *
 * State is persisted per operation/scope pair as a single JSON file.
 * Writes use a write-to-temp-then-rename strategy to prevent torn reads
 * under concurrent FPM workers or any forked process model.
 *
 * Half-open state: after cooldown the breaker allows one probe request through.
 * On success → recordSuccess() clears the state file (closed).
 * On failure → recordFailure() re-opens immediately.
 */
final readonly class VendorOutboundCircuitBreakerService implements VendorOutboundCircuitBreakerServiceInterface
{
    public function __construct(private string $stateDir)
    {
    }

    /**
     * @return array{operation:string,scopeKey:string,state:string,failureCount:int,threshold:int,cooldownSeconds:int,allowRequest:bool}
     */
    public function currentState(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $payload = $this->readState($operation, $scopeKey);

        if ('open' === $payload['state'] && null !== $payload['openedAt']) {
            if ((time() - $payload['openedAt']) >= $cooldownSeconds) {
                return $this->dto($operation, $scopeKey, 'half_open', $payload['failureCount'], $threshold, $cooldownSeconds, true);
            }

            return $this->dto($operation, $scopeKey, 'open', $payload['failureCount'], $threshold, $cooldownSeconds, false);
        }

        return $this->dto($operation, $scopeKey, 'closed', $payload['failureCount'], $threshold, $cooldownSeconds, true);
    }

    public function recordSuccess(string $operation, string $scopeKey): void
    {
        $path = $this->filePath($operation, $scopeKey);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * @return array{operation:string,scopeKey:string,state:string,failureCount:int,threshold:int,cooldownSeconds:int,allowRequest:bool}
     */
    public function recordFailure(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $current = $this->readState($operation, $scopeKey);
        $failureCount = max(0, $current['failureCount']) + 1;
        $open = $failureCount >= $threshold;
        $state = $open ? 'open' : 'closed';

        $this->writeState($operation, $scopeKey, [
            'failureCount' => $failureCount,
            'state' => $state,
            'openedAt' => $open ? time() : null,
        ]);

        return $this->dto($operation, $scopeKey, $state, $failureCount, $threshold, $cooldownSeconds, !$open);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * @return array{failureCount:int,state:string,openedAt:int|null}
     */
    private function readState(string $operation, string $scopeKey): array
    {
        $path = $this->filePath($operation, $scopeKey);

        if (!is_file($path)) {
            return $this->emptyPayload();
        }

        $contents = @file_get_contents($path);
        if (false === $contents || '' === $contents) {
            return $this->emptyPayload();
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            return $this->emptyPayload();
        }

        $payload = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key)) {
                $payload[$key] = $value;
            }
        }

        return $this->normalizePayload($payload);
    }

    /**
     * Atomic write: tmp file → rename.
     * Prevents torn reads under concurrent FPM workers.
     *
     * @param array{failureCount:int,state:string,openedAt:int|null} $payload
     */
    private function writeState(string $operation, string $scopeKey, array $payload): void
    {
        $dir = rtrim($this->stateDir, DIRECTORY_SEPARATOR);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Unable to create circuit-breaker state directory "%s".', $dir));
        }

        $target = $this->filePath($operation, $scopeKey);
        $tmp = $target.'.tmp.'.getmypid();

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        if (false === @file_put_contents($tmp, $json, LOCK_EX)) {
            throw new \RuntimeException(sprintf('Unable to write circuit-breaker tmp state to "%s".', $tmp));
        }

        if (!@rename($tmp, $target)) {
            @unlink($tmp);
            throw new \RuntimeException(sprintf('Unable to atomically commit circuit-breaker state to "%s".', $target));
        }
    }

    private function filePath(string $operation, string $scopeKey): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $operation.'__'.$scopeKey);
        $safe = is_string($safe) ? $safe : sha1($operation.'__'.$scopeKey);

        return rtrim($this->stateDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$safe.'.json';
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array{failureCount:int,state:string,openedAt:int|null}
     */
    private function normalizePayload(array $payload): array
    {
        $fc = $payload['failureCount'] ?? 0;
        $st = $payload['state'] ?? 'closed';
        $oa = $payload['openedAt'] ?? null;

        return [
            'failureCount' => is_numeric($fc) ? max(0, (int) $fc) : 0,
            'state' => is_string($st) && '' !== trim($st) ? trim($st) : 'closed',
            'openedAt' => is_numeric($oa) ? (int) $oa : null,
        ];
    }

    /**
     * @return array{failureCount:int,state:string,openedAt:int|null}
     */
    private function emptyPayload(): array
    {
        return ['failureCount' => 0, 'state' => 'closed', 'openedAt' => null];
    }

    /**
     * @return array{operation:string,scopeKey:string,state:string,failureCount:int,threshold:int,cooldownSeconds:int,allowRequest:bool}
     */
    private function dto(
        string $operation,
        string $scopeKey,
        string $state,
        int $failureCount,
        int $threshold,
        int $cooldownSeconds,
        bool $allowRequest,
    ): array {
        return (new VendorOutboundCircuitBreakerStateDTO(
            operation: $operation,
            scopeKey: $scopeKey,
            state: $state,
            failureCount: $failureCount,
            threshold: $threshold,
            cooldownSeconds: $cooldownSeconds,
            allowRequest: $allowRequest,
        ))->toArray();
    }
}
