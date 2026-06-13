<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Media;

use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaFallbackEvaluatedEventInterface;

interface VendorCatalogDestinationMediaFallbackServiceInterface
{
    public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): VendorCategoryDestinationMediaFallbackEvaluatedEventInterface;
}
