<?php

declare(strict_types=1);

namespace App\Service\Reliability;

use App\DTO\Reliability\OutboundCircuitBreakerStateDTO;
use App\ServiceInterface\Reliability\OutboundCircuitBreakerInterface;
use JsonException;

/**
 * File-backed circuit-breaker implementation for outbound runtime protections.
 *
 * The breaker persists minimal failure state per operation/scope pair so that
 * repeated downstream failures can short-circuit unstable transport paths across
 * requests in one local runtime environment.
 */
final readonly class FileOutboundCircuitBreaker implements OutboundCircuitBreakerInterface
{
    public function __construct(private string $stateDir)
    {
    }

    public function currentState(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $payload = $this->readState($operation, $scopeKey);
        $failureCount = (int) ($payload['failureCount'] ?? 0);
        $openedAt = isset($payload['openedAt']) ? (int) $payload['openedAt'] : null;
        $state = (string) ($payload['state'] ?? 'closed');

        if ('open' === $state && null !== $openedAt) {
            if ((time() - $openedAt) >= $cooldownSeconds) {
                return $this->createState(new OutboundCircuitBreakerStateDTO(
                    operation: $operation,
                    scopeKey: $scopeKey,
                    state: 'half_open',
                    failureCount: $failureCount,
                    threshold: $threshold,
                    cooldownSeconds: $cooldownSeconds,
                    allowRequest: true,
                ));
            }

            return $this->createState(new OutboundCircuitBreakerStateDTO(
                operation: $operation,
                scopeKey: $scopeKey,
                state: 'open',
                failureCount: $failureCount,
                threshold: $threshold,
                cooldownSeconds: $cooldownSeconds,
                allowRequest: false,
            ));
        }

        return $this->createState(new OutboundCircuitBreakerStateDTO(
            operation: $operation,
            scopeKey: $scopeKey,
            state: 'closed',
            failureCount: $failureCount,
            threshold: $threshold,
            cooldownSeconds: $cooldownSeconds,
            allowRequest: true,
        ));
    }

    public function recordSuccess(string $operation, string $scopeKey): void
    {
        $path = $this->filePath($operation, $scopeKey);
        if (is_file($path)) {
            unlink($path);
        }
    }

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

        return $this->createState(new OutboundCircuitBreakerStateDTO(
            operation: $operation,
            scopeKey: $scopeKey,
            state: $state,
            failureCount: $failureCount,
            threshold: $threshold,
            cooldownSeconds: $cooldownSeconds,
            allowRequest: 'open' !== $state,
        ));
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

        $contents = file_get_contents($path);
        if (false === $contents) {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param array{failureCount:int, state:string, openedAt:int|null} $payload
     * @throws JsonException
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
        if (!is_string($safe)) {
            $safe = sha1($operation.'__'.$scopeKey);
        }

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
    private function createState(OutboundCircuitBreakerStateDTO $state): array
    {
        return $state->toArray();
    }
}
