<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategoryDestinationMediaFallbackEvaluatedInterface;

final class CategoryDestinationMediaFallbackEvaluated extends AbstractPayloadEvent implements CategoryDestinationMediaFallbackEvaluatedInterface {}
