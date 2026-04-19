<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationGovernanceTrailRequestDTO;
use App\Vendoring\EventInterface\CategorySyndicationGovernanceTrailRecordedInterface;

interface CatalogSyndicationGovernanceTrailServiceInterface
{
    public function recordTrail(CatalogSyndicationGovernanceTrailRequestDTO $request): CategorySyndicationGovernanceTrailRecordedInterface;
}
