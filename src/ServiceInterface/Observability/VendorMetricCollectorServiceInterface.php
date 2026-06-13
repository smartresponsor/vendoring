<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * Write-side contract for recording runtime metrics.
 *
 * Implementations may forward increments to logs, memory, or external observability
 * backends, but they must preserve metric nameEntity and normalized string tags.
 */
interface VendorMetricCollectorServiceInterface
{
    /**
     * Increment one named metric.
     *
     * @param string                $nameEntity stable metric nameEntity
     * @param array<string, string> $tags       normalized dimension tags such as route, outcome,
     *                                          scope, or capability
     */
    public function increment(string $nameEntity, array $tags = []): void;
}
