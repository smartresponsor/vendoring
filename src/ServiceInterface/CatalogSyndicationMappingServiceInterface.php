<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\EventInterface\CategorySyndicationPublishPackageBuiltInterface;

interface CatalogSyndicationMappingServiceInterface
{
    public function buildPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPublishPackageBuiltInterface;
}
