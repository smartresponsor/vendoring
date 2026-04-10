<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\EventInterface\CategorySyndicationFallbackAwarePackageGatedInterface;

interface CatalogSyndicationFallbackAwarePackageGateServiceInterface
{
    public function buildGatedPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationFallbackAwarePackageGatedInterface;
}
