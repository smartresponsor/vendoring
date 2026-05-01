<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Media;

use App\Vendoring\Event\Vendor\VendorCategoryDestinationMediaFallbackEvaluatedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaFallbackEvaluatedEventInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaFallbackServiceInterface;
use DateTimeImmutable;

final class VendorCatalogDestinationMediaFallbackService implements VendorCatalogDestinationMediaFallbackServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): VendorCategoryDestinationMediaFallbackEvaluatedEventInterface
    {
        return new VendorCategoryDestinationMediaFallbackEvaluatedEvent([
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
