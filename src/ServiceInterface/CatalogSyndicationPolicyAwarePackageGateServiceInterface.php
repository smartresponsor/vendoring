<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;

interface CatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    public function buildGatedPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPolicyAwarePackageGatedInterface;
}
