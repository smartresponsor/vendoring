<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;

final class CategorySyndicationPolicyAwarePackageGated extends AbstractPayloadEvent implements CategorySyndicationPolicyAwarePackageGatedInterface {}
