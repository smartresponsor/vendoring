<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationPolicyAwarePackageGatedEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationPolicyAwarePackageGatedEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationPolicyAwarePackageGatedEventInterface {}
