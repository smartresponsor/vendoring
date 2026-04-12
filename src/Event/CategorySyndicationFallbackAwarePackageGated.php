<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategorySyndicationFallbackAwarePackageGatedInterface;

final class CategorySyndicationFallbackAwarePackageGated extends AbstractPayloadEvent implements CategorySyndicationFallbackAwarePackageGatedInterface {}
