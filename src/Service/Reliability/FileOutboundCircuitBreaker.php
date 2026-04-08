<?php

declare(strict_types=1);

namespace App\Service\Reliability;

use App\ServiceInterface\Reliability\OutboundCircuitBreakerInterface;

/**
 * File-backed circuit-breaker implementation for outbound runtime protections.
 *
 * The breaker persists minimal failure state per operation/scope pair so that
 * repeated downstream failures can short-circuit unstable transport paths across
 * requests in one local runtime environment.
 */
final class FileOutboundCircuitBreaker implements OutboundCircuitBreakerInterface
{
    public function __construct(private readonly string $stateDir)
    {
    }

    /**
     * Executes the current state operation for this runtime surface.
     */
    public function currentState(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $payload = $this->readState($operation, $scopeKey);
        $failureCount = (int) ($payload['failureCount'] ?? 0);
        $openedAt = isset($payload['openedAt']) ? (int) $payload['openedAt'] : null;
        $state = (string) ($payload['state'] ?? 'closed');

        if ('open' === $state && null !== $openedAt) {
            if ((time() - $openedAt) >= $cooldownSeconds) {
                return $this->statePayload($operation, $scopeKey, 'half_open', $failureCount, $threshold, $cooldownSeconds, true);
            }

            return $this->statePayload($operation, $scopeKey, 'open', $failureCount, $threshold, $cooldownSeconds, false);
        }

        return $this->statePayload($operation, $scopeKey, 'closed', $failureCount, $threshold, $cooldownSeconds, true);
    }

    /**
     * Records the requested runtime state change.
     */
    public function recordSuccess(string $operation, string $scopeKey): void
    {
        $path = $this->filePath($operation, $scopeKey);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Records the requested runtime state change.
     */
    public function recordFailure(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $current = $this->readState($operation, $scopeKey);
        $failureCount = max(0, (int) ($current['failureCount'] ?? 0)) + 1;
        $state = $failureCount >= $threshold ? 'open' : 'closed';
        $openedAt = 'open' === $state ? time() : null;

        $this->writeState($operation, $scopeKey, [
            'failureCount' => $failureCount,
            'state' => $state,
            'openedAt' => $openedAt,
        ]);

        return $this->statePayload(
            $operation,
            $scopeKey,
            $state,
            $failureCount,
            $threshold,
            $cooldownSeconds,
            'open' !== $state,
        );
    }

    /**
     * @return array{failureCount?:int, state?:string, openedAt?:int|null}
     */
    private function readState(string $operation, string $scopeKey): array
    {
        $path = $this->filePath($operation, $scopeKey);
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param array{failureCount:int, state:string, openedAt:int|null} $payload
     */
    private function writeState(string $operation, string $scopeKey, array $payload): void
    {
        $dir = rtrim($this->stateDir, DIRECTORY_SEPARATOR);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->filePath($operation, $scopeKey), json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function filePath(string $operation, string $scopeKey): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $operation.'__'.$scopeKey);

        return rtrim($this->stateDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$safe.'.json';
    }

    /**
     * @return array{
     *   operation:string,
     *   scopeKey:string,
     *   state:string,
     *   failureCount:int,
     *   threshold:int,
     *   cooldownSeconds:int,
     *   allowRequest:bool
     * }
     */
    private function statePayload(
        string $operation,
        string $scopeKey,
        string $state,
        int $failureCount,
        int $threshold,
        int $cooldownSeconds,
        bool $allowRequest,
    ): array {
        return [
            'operation' => $operation,
            'scopeKey' => $scopeKey,
            'state' => $state,
            'failureCount' => $failureCount,
            'threshold' => $threshold,
            'cooldownSeconds' => $cooldownSeconds,
            'allowRequest' => $allowRequest,
        ];
    }
}
