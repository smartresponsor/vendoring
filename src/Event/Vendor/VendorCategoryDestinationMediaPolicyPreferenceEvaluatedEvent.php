<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface {}
