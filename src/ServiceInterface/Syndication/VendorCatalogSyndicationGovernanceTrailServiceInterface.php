<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationGovernanceTrailRequestDTO;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationGovernanceTrailRecordedEventInterface;

interface VendorCatalogSyndicationGovernanceTrailServiceInterface
{
    public function recordTrail(VendorCatalogSyndicationGovernanceTrailRequestDTO $request): VendorCategorySyndicationGovernanceTrailRecordedEventInterface;
}
