<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategorySyndicationPolicyAwarePackageGatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationPolicyAwarePackageGatedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationPolicyAwarePackageGatedEventInterface
{
}
