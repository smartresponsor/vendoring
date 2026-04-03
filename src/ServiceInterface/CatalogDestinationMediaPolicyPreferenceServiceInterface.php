<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;

interface CatalogDestinationMediaPolicyPreferenceServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;
}
