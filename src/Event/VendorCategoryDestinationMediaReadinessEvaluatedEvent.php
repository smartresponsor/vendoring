<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategoryDestinationMediaReadinessEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaReadinessEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaReadinessEvaluatedEventInterface
{
}
