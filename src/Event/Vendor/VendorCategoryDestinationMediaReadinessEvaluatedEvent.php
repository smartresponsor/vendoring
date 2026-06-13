<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaReadinessEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaReadinessEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaReadinessEvaluatedEventInterface {}
