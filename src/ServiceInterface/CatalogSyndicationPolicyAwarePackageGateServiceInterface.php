<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;

interface CatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    /**
     * @param array<string, mixed>  $categoryData
     * @param array<string, string> $fieldMap
     * @param list<string>          $requiredFields
     */
    public function buildGatedPublishPackage(string $packageId, string $destinationId, string $categoryId, string $version, string $localeMode, array $categoryData, array $fieldMap, array $requiredFields, string $actorId, string $reason): CategorySyndicationPolicyAwarePackageGatedInterface;
}
