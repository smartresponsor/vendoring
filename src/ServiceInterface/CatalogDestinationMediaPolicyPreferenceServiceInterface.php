<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;

/**
 * Application contract for catalog destination media policy preference service operations.
 */
interface CatalogDestinationMediaPolicyPreferenceServiceInterface
{
    /**
     * Evaluates the requested runtime decision.
     */
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;
}
