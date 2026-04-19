<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Event\CategoryDestinationMediaReadinessEvaluated;
use App\Vendoring\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;
use App\Vendoring\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
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
