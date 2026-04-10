<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\CatalogSyndication\CatalogSyndicationGovernanceTrailRequestDTO;
use App\EventInterface\CategorySyndicationGovernanceTrailRecordedInterface;

interface CatalogSyndicationGovernanceTrailServiceInterface
{
    public function recordTrail(CatalogSyndicationGovernanceTrailRequestDTO $request): CategorySyndicationGovernanceTrailRecordedInterface;
}
