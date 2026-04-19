<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\CategoryDestinationMediaFallbackEvaluatedInterface;

final class CategoryDestinationMediaFallbackEvaluated extends AbstractPayloadEvent implements CategoryDestinationMediaFallbackEvaluatedInterface {}
