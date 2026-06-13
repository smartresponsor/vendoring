<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Traffic;

use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;

interface VendorWriteRateLimiterServiceInterface
{
    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject;
}
