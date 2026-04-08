<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategoryDestinationMediaFallbackEvaluatedInterface;

/**
 * Application contract for catalog destination media fallback service operations.
 */
interface CatalogDestinationMediaFallbackServiceInterface
{
    /**
     * Evaluates the requested runtime decision.
     */
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaFallbackEvaluatedInterface;
}
