<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategorySyndicationPublishPackageBuiltInterface;

final class CategorySyndicationPublishPackageBuilt extends AbstractPayloadEvent implements CategorySyndicationPublishPackageBuiltInterface {}
