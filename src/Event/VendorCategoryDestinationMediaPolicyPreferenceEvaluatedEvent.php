<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent extends VendorAbstractPayloadEvent implements VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface
{
}
