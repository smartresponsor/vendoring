<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationGovernanceTrailRecordedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationGovernanceTrailRecordedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationGovernanceTrailRecordedEventInterface {}
