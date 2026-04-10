<?php

declare(strict_types=1);

namespace App\Service;

use App\Event\CategoryDestinationMediaFallbackEvaluated;
use App\EventInterface\CategoryDestinationMediaFallbackEvaluatedInterface;
use App\ServiceInterface\CatalogDestinationMediaFallbackServiceInterface;
use DateTimeImmutable;

final class CatalogDestinationMediaFallbackService implements CatalogDestinationMediaFallbackServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaFallbackEvaluatedInterface
    {
        return new CategoryDestinationMediaFallbackEvaluated([
            'destinationId' => trim($destinationId),
            'categoryId' => trim($categoryId),
            'requiredMissing' => [],
            'warnings' => [],
            'checks' => ['fallbackMediaReady' => true],
            'exactMatchedBindingIds' => [],
            'fallbackMatchedBindingIds' => [],
            'actorId' => trim($actorId),
            'reason' => trim($reason),
        ], new DateTimeImmutable());
    }
}
