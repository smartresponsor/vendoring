<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationFallbackAwarePackageGatedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationFallbackAwarePackageGatedEventInterface {}
