<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaFallbackEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaFallbackEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaFallbackEvaluatedEventInterface {}
