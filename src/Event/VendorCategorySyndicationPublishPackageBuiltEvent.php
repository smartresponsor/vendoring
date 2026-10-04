<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\VendorCategorySyndicationPublishPackageBuiltEventInterface;

/**
 * Immutable catalog/syndication payload event.
 */
final class VendorCategorySyndicationPublishPackageBuiltEvent extends VendorAbstractPayloadEvent implements VendorCategorySyndicationPublishPackageBuiltEventInterface
{
}
