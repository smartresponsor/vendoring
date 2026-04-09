<?php

declare(strict_types=1);

namespace App\Service;

use App\Event\CategoryDestinationMediaPolicyPreferenceEvaluated;
use App\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;
use App\ServiceInterface\CatalogDestinationMediaPolicyPreferenceServiceInterface;

final class CatalogDestinationMediaPolicyPreferenceService implements CatalogDestinationMediaPolicyPreferenceServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): CategoryDestinationMediaPolicyPreferenceEvaluatedInterface
    {
        return new CategoryDestinationMediaPolicyPreferenceEvaluated([
            'destinationId' => trim($destinationId),
            'categoryId' => trim($categoryId),
            'mediaPolicyMode' => 'prefer_exact',
            'strictPublishable' => true,
            'fallbackPublishable' => true,
            'resolvedPublishable' => true,
            'fallbackUsed' => false,
            'requiredMissing' => [],
            'warnings' => [],
            'checks' => ['resolvedPublishable' => true],
            'actorId' => trim($actorId),
            'reason' => trim($reason),
        ], new \DateTimeImmutable());
    }
}
