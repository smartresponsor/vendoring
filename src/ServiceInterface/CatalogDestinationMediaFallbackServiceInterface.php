<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\EventInterface\CategoryDestinationMediaFallbackEvaluatedInterface;

interface CatalogDestinationMediaFallbackServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaFallbackEvaluatedInterface;
}
