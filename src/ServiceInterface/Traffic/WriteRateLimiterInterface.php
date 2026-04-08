<?php

declare(strict_types=1);

namespace App\ServiceInterface\Traffic;

use App\ValueObject\Traffic\WriteRateLimitDecision;

/**
 * Application contract for write rate limiter operations.
 */
interface WriteRateLimiterInterface
{
    /**
     * Executes the consume operation for this runtime surface.
     */
    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): WriteRateLimitDecision;
}
