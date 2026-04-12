<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\CategoryDestinationMediaPolicyPreferenceEvaluatedInterface;

final class CategoryDestinationMediaPolicyPreferenceEvaluated extends AbstractPayloadEvent implements CategoryDestinationMediaPolicyPreferenceEvaluatedInterface {}
