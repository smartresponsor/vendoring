<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Traffic;

use App\Vendoring\ServiceInterface\Traffic\VendorWriteRateLimiterServiceInterface;
use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;

/**
 * Write rate limiter with pluggable backend strategy.
 *
 * The limiter accepts a callable factory that produces per-bucket decision arrays,
 * allowing the host application to inject any backend (Symfony RateLimiter, Redis,
 * APCu, or a test double) without a hard compile-time dependency on a specific package.
 *
 * Default (no backend injected): fail-open — all requests are allowed.
 * This is a safe default for environments where rate limiting is not yet configured.
 * Override by injecting a factory in the host application's services.yaml.
 *
 * Host application wiring example using Symfony RateLimiter:
 *
 *   App\Vendoring\Service\Traffic\VendorWriteRateLimiterService:
 *       arguments:
 *           $backendFactory: !closure
 *               '@limiter.vendor_write'
 *
 * Or via a decorated service that wraps RateLimiterFactory.
 *
 * @phpstan-type BackendDecision array{allowed: bool, remaining: int, retryAfterSeconds: int}
 */
final class VendorWriteRateLimiterService implements VendorWriteRateLimiterServiceInterface
{
    /**
     * @param callable(string $bucketId, int $limit): BackendDecision|null $backendFactory
     */
    public function __construct(
        private readonly mixed $backendFactory = null,
    ) {
    }

    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject
    {
        $normalizedScope = trim($scope);
        $normalizedActorKey = trim($actorKey);

        if ($limit < 1 || $windowSeconds < 1 || '' === $normalizedScope || '' === $normalizedActorKey) {
            return new VendorWriteRateLimitDecisionValueObject(true, max(1, $limit), max(0, $limit - 1), 0);
        }

        if (null === $this->backendFactory) {
            // Fail-open: no backend configured — allow all requests.
            return new VendorWriteRateLimitDecisionValueObject(true, $limit, max(0, $limit - 1), 0);
        }

        $bucketId = $normalizedScope.'.'.$normalizedActorKey;

        /** @var BackendDecision $decision */
        $decision = ($this->backendFactory)($bucketId, $limit);

        return new VendorWriteRateLimitDecisionValueObject(
            $decision['allowed'],
            $limit,
            $decision['remaining'],
            $decision['retryAfterSeconds'],
        );
    }
}
