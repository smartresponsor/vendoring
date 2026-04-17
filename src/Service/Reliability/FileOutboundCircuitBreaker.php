<?php

declare(strict_types=1);

namespace App\Service\Reliability;

use App\DTO\Reliability\OutboundCircuitBreakerStateDTO;
use App\ServiceInterface\Reliability\OutboundCircuitBreakerInterface;
use JsonException;
use RuntimeException;

/**
 * File-backed circuit-breaker implementation for outbound runtime protections.
 *
 * The breaker persists minimal failure state per operation/scope pair so that
 * repeated downstream failures can short-circuit unstable transport paths across
 * requests in one local runtime environment.
 */
final readonly class FileOutboundCircuitBreaker implements OutboundCircuitBreakerInterface
{
    public function __construct(private string $stateDir) {}

    public function currentState(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $payload = $this->readState($operation, $scopeKey);
        $failureCount = $payload['failureCount'];
        $openedAt = $payload['openedAt'];
        $state = $payload['state'];

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
     * @throws JsonException
     */
    public function recordFailure(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array
    {
        $current = $this->readState($operation, $scopeKey);
        $failureCount = max(0, $current['failureCount']) + 1;
        $state = $failureCount >= $threshold ? 'open' : 'closed';
        $openedAt = 'open' === $state ? time() : null;

        /** @var array{failureCount:int, state:string, openedAt:int|null} $statePayload */
        $statePayload = [
            'failureCount' => $failureCount,
            'state' => $state,
            'openedAt' => $openedAt,
        ];

        $this->writeState($operation, $scopeKey, $statePayload);

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
     * @return array{failureCount:int, state:string, openedAt:int|null}
     */
    private function readState(string $operation, string $scopeKey): array
    {
        $path = $this->filePath($operation, $scopeKey);
        if (!is_file($path)) {
            return $this->emptyStatePayload();
        }

        $contents = file_get_contents($path);
        if (false === $contents) {
            return $this->emptyStatePayload();
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return $this->emptyStatePayload();
        }

        /** @var array<string, mixed> $decoded */
        return $this->normalizeStatePayload($decoded);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{failureCount:int, state:string, openedAt:int|null}
     */
    private function normalizeStatePayload(array $payload): array
    {
        return [
            'failureCount' => $this->normalizeFailureCount($payload['failureCount'] ?? 0),
            'state' => $this->normalizeStateName($payload['state'] ?? 'closed'),
            'openedAt' => $this->normalizeOpenedAt($payload['openedAt'] ?? null),
        ];
    }



    private function normalizeFailureCount(mixed $value): int
    {
        return is_numeric($value) ? max(0, (int) $value) : 0;
    }

    private function normalizeStateName(mixed $value): string
    {
        if (!is_string($value)) {
            return 'closed';
        }

        $trimmed = trim($value);

        return '' !== $trimmed ? $trimmed : 'closed';
    }

    private function normalizeOpenedAt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @return array{failureCount:int, state:string, openedAt:int|null}
     */
    private function emptyStatePayload(): array
    {
        return [
            'failureCount' => 0,
            'state' => 'closed',
            'openedAt' => null,
        ];
    }

    /**
     * @param array{failureCount:int, state:string, openedAt:int|null} $payload
     * @throws JsonException
     */
    private function writeState(string $operation, string $scopeKey, array $payload): void
    {
        $directory = rtrim($this->stateDir, DIRECTORY_SEPARATOR);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create circuit-breaker state directory "%s".', $directory));
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        if (false === file_put_contents($this->filePath($operation, $scopeKey), $json)) {
            throw new RuntimeException('Unable to persist circuit-breaker state.');
        }
    }

    private function filePath(string $operation, string $scopeKey): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $operation . '__' . $scopeKey);
        if (!is_string($safe)) {
            $safe = sha1($operation . '__' . $scopeKey);
        }

        return rtrim($this->stateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe . '.json';
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
