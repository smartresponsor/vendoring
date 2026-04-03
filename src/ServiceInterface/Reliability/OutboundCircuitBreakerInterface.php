<?php

declare(strict_types=1);

namespace App\ServiceInterface\Reliability;

/**
 * Stateful contract for outbound circuit-breaker decisions.
 *
 * Implementations keep per-operation and per-scope failure state so that callers
 * can short-circuit unstable downstream paths before they amplify runtime errors.
 */
interface OutboundCircuitBreakerInterface
{
    /**
     * Read the current breaker state for one operation/scope pair.
     *
     * @param string $operation       Stable outbound operation name.
     * @param string $scopeKey        Stable scope key such as `tenant:vendor`.
     * @param int    $threshold       Number of consecutive failures required to open the breaker.
     * @param int    $cooldownSeconds Cooldown duration before a half-open probe is allowed.
     *
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

    /**
     * Reset breaker state after a successful outbound call.
     */
    public function recordSuccess(string $operation, string $scopeKey): void;

    /**
     * Record one outbound failure and return the updated breaker state.
     *
     * @param string $operation       Stable outbound operation name.
     * @param string $scopeKey        Stable scope key such as `tenant:vendor`.
     * @param int    $threshold       Number of consecutive failures required to open the breaker.
     * @param int    $cooldownSeconds Cooldown duration before a half-open probe is allowed.
     *
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
    public function recordFailure(string $operation, string $scopeKey, int $threshold, int $cooldownSeconds): array;
}
