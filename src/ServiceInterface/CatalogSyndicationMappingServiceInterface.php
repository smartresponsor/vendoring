<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategorySyndicationPublishPackageBuiltInterface;

/**
 * Application contract for catalog syndication mapping service operations.
 */
interface CatalogSyndicationMappingServiceInterface
{
    /**
     * @param array<string, mixed>  $categoryData
     * @param array<string, string> $fieldMap
     * @param list<string>          $requiredFields
     */
    public function buildPublishPackage(
        string $packageId,
        string $destinationId,
        string $categoryId,
        string $version,
        string $localeMode,
        array $categoryData,
        array $fieldMap,
        array $requiredFields,
        string $actorId,
        string $reason,
    ): CategorySyndicationPublishPackageBuiltInterface;
}
