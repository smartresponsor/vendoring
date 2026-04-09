<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;

interface CatalogDestinationMediaReadinessServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaReadinessEvaluatedInterface;
}
