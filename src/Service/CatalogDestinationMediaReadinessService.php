<?php

declare(strict_types=1);

namespace App\Service;

use App\Event\CategoryDestinationMediaReadinessEvaluated;
use App\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;
use App\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
use DateTimeImmutable;

final class CatalogDestinationMediaReadinessService implements CatalogDestinationMediaReadinessServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaReadinessEvaluatedInterface
    {
        return new CategoryDestinationMediaReadinessEvaluated([
            'destinationId' => trim($destinationId),
            'categoryId' => trim($categoryId),
            'requiredMissing' => [],
            'warnings' => [],
            'checks' => ['strictMediaReady' => true],
            'actorId' => trim($actorId),
            'reason' => trim($reason),
        ], new DateTimeImmutable());
    }
}
