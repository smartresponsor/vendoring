<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;

/**
 * Application contract for catalog destination media readiness service operations.
 */
interface CatalogDestinationMediaReadinessServiceInterface
{
    /**
     * Evaluates the requested runtime decision.
     */
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaReadinessEvaluatedInterface;
}
