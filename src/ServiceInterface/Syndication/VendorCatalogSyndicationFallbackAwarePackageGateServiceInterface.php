<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEventInterface;

/**
 * @noinspection PhpClassNamingConventionInspection
 */
interface VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface
{
    public function buildGatedPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): VendorCategorySyndicationFallbackAwarePackageGatedEventInterface;
}
