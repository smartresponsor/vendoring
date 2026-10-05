<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategorySyndicationFallbackAwarePackageGatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationFallbackAwarePackageGatedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationFallbackAwarePackageGatedEventInterface
{
}
