<?php

declare(strict_types=1);

namespace App\DTO\CatalogSyndication;

final readonly class CatalogSyndicationPublishPackageRequestDTO
{
    /**
     * @param array<string, mixed>  $categoryData
     * @param array<string, string> $fieldMap
     * @param list<string>          $requiredFields
     */
    public function __construct(
        public string $packageId,
        public string $destinationId,
        public string $categoryId,
        public string $version,
        public string $localeMode,
        public array $categoryData,
        public array $fieldMap,
        public array $requiredFields,
        public string $actorId,
        public string $reason,
    ) {
    }
}
