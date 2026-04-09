<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Event\CategorySyndicationPublishPackageBuilt;
use App\EventInterface\CategorySyndicationPublishPackageBuiltInterface;
use App\PolicyInterface\CategorySyndicationMappingPolicyInterface;
use App\ServiceInterface\CatalogSyndicationMappingServiceInterface;
use App\ValueObject\CategorySyndicationMappingProfile;
use App\ValueObject\CategorySyndicationPublishPackage;

final readonly class CatalogSyndicationMappingService implements CatalogSyndicationMappingServiceInterface
{
    public function __construct(
        private CategorySyndicationMappingPolicyInterface $policy,
    ) {
    }

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
    ): CategorySyndicationPublishPackageBuiltInterface {
        $this->policy->assertLocaleMode($localeMode);
        $normalizedFieldMap = $this->policy->normalizeFieldMap($fieldMap);
        $normalizedRequiredFields = $this->policy->normalizeRequiredFields($requiredFields);

        $profile = new CategorySyndicationMappingProfile(
            trim($destinationId),
            trim($version),
            $normalizedFieldMap,
            $normalizedRequiredFields,
            trim($localeMode),
        );

        $payload = [];
        foreach ($profile->fieldMap() as $sourceField => $targetField) {
            $payload[$targetField] = $categoryData[$sourceField] ?? null;
        }

        $missingRequiredFields = [];
        foreach ($profile->requiredFields() as $requiredField) {
            $mappedValue = $payload[$requiredField] ?? null;
            if (null === $mappedValue || '' === self::stringOrEmpty($mappedValue)) {
                $missingRequiredFields[] = $requiredField;
            }
        }

        $package = new CategorySyndicationPublishPackage(
            trim($packageId),
            $profile->destinationId(),
            trim($categoryId),
            $profile->version(),
            $profile->localeMode(),
            $payload,
            $missingRequiredFields,
            [] === $missingRequiredFields,
        );

        return new CategorySyndicationPublishPackageBuilt(
            [
                'packageId' => $package->packageId(),
                'destinationId' => $package->destinationId(),
                'categoryId' => $package->categoryId(),
                'version' => $package->version(),
                'localeMode' => $package->localeMode(),
                'payload' => $package->payload(),
                'missingRequiredFields' => $package->missingRequiredFields(),
                'publishable' => $package->publishable(),
                'fieldMap' => $profile->fieldMap(),
                'requiredFields' => $profile->requiredFields(),
                'actorId' => trim($actorId),
                'reason' => trim($reason),
            ],
            new \DateTimeImmutable('now'),
        );
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
