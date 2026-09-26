<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategorySyndicationGovernanceTrailRecordedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationGovernanceTrailRecordedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationGovernanceTrailRecordedEventInterface
{
}
