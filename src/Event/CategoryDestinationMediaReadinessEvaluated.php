<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;

final class CategoryDestinationMediaReadinessEvaluated extends AbstractPayloadEvent implements CategoryDestinationMediaReadinessEvaluatedInterface {}
