<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Traffic;

use App\Vendoring\ValueObject\Traffic\WriteRateLimitDecision;

interface WriteRateLimiterInterface
{
    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): WriteRateLimitDecision;
}
