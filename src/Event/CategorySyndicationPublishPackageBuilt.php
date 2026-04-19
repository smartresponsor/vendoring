<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\CategorySyndicationPublishPackageBuiltInterface;

final class CategorySyndicationPublishPackageBuilt extends AbstractPayloadEvent implements CategorySyndicationPublishPackageBuiltInterface {}
