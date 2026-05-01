<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Media;

use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaReadinessEvaluatedEventInterface;

interface VendorCatalogDestinationMediaReadinessServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): VendorCategoryDestinationMediaReadinessEvaluatedEventInterface;
}
