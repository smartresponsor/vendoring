<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationPublishPackageBuiltEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationPublishPackageBuiltEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationPublishPackageBuiltEventInterface {}
