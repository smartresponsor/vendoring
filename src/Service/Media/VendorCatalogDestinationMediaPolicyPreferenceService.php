<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Media;

use App\Vendoring\Event\Vendor\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaPolicyPreferenceServiceInterface;
use DateTimeImmutable;

final class VendorCatalogDestinationMediaPolicyPreferenceService implements VendorCatalogDestinationMediaPolicyPreferenceServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface
    {
        return new VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent([
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
