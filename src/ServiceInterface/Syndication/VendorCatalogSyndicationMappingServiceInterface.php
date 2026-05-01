<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationPublishPackageBuiltEventInterface;

interface VendorCatalogSyndicationMappingServiceInterface
{
    public function buildPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): VendorCategorySyndicationPublishPackageBuiltEventInterface;
}
