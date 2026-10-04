<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategoryDestinationMediaFallbackEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaFallbackEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaFallbackEvaluatedEventInterface
{
}
