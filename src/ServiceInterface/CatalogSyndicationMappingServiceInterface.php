<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\EventInterface\CategorySyndicationPublishPackageBuiltInterface;

interface CatalogSyndicationMappingServiceInterface
{
    public function buildPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPublishPackageBuiltInterface;
}
