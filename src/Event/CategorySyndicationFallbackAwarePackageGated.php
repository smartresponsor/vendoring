<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\CategorySyndicationFallbackAwarePackageGatedInterface;

final class CategorySyndicationFallbackAwarePackageGated extends AbstractPayloadEvent implements CategorySyndicationFallbackAwarePackageGatedInterface {}
