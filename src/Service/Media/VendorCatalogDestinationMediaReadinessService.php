<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Media;

use App\Vendoring\Event\Vendor\VendorCategoryDestinationMediaReadinessEvaluatedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaReadinessEvaluatedEventInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaReadinessServiceInterface;
use DateTimeImmutable;

final class VendorCatalogDestinationMediaReadinessService implements VendorCatalogDestinationMediaReadinessServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): VendorCategoryDestinationMediaReadinessEvaluatedEventInterface
    {
        return new VendorCategoryDestinationMediaReadinessEvaluatedEvent([
            'destinationId' => trim($destinationId),
            'categoryId' => trim($categoryId),
            'requiredMissing' => [],
            'warnings' => [],
            'checks' => ['strictMediaReady' => true],
            'actorId' => trim($actorId),
            'reason' => trim($reason),
        ], new DateTimeImmutable());
    }
}
