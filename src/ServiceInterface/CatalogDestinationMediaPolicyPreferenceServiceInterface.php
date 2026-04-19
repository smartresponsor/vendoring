<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;

interface CatalogDestinationMediaPolicyPreferenceServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;
}
