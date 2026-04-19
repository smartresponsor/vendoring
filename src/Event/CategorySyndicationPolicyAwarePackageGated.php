<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;

final class CategorySyndicationPolicyAwarePackageGated extends AbstractPayloadEvent implements CategorySyndicationPolicyAwarePackageGatedInterface {}
