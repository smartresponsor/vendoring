<?php

declare(strict_types=1);

namespace App\ServiceInterface\Reliability;

use JsonException;
use RuntimeException;

/**
 * Stateful contract for outbound circuit-breaker decisions.
 *
 * Implementations keep per-operation and per-scope failure state so that callers
 * can short-circuit unstable downstream paths before they amplify runtime errors.
 */
interface OutboundCircuitBreakerInterface
{
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
    public function currentState(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array;

    public function recordSuccess(string $operation, string $scopeKey): void;

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
     * @throws RuntimeException
     */
    public function recordFailure(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array;
}
