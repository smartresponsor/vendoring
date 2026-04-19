<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Event\CategoryDestinationMediaPolicyPreferenceEvaluated;
use App\Vendoring\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;
use App\Vendoring\ServiceInterface\CatalogDestinationMediaPolicyPreferenceServiceInterface;
use DateTimeImmutable;

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
        ], new DateTimeImmutable());
    }
}
