<?php

declare(strict_types=1);

namespace App\ServiceInterface\Traffic;

use App\ValueObject\Traffic\WriteRateLimitDecision;

interface WriteRateLimiterInterface
{
    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): WriteRateLimitDecision;
}
