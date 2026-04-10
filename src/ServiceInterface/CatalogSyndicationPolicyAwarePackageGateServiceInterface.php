<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;

interface CatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    public function buildGatedPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPolicyAwarePackageGatedInterface;
}
