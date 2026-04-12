<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface;

final class CategoryDestinationMediaReadinessEvaluated extends AbstractPayloadEvent implements CategoryDestinationMediaReadinessEvaluatedInterface {}
