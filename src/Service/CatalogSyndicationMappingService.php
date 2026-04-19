<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\Event\CategorySyndicationPublishPackageBuilt;
use App\Vendoring\EventInterface\CategorySyndicationPublishPackageBuiltInterface;
use App\Vendoring\PolicyInterface\CategorySyndicationMappingPolicyInterface;
use App\Vendoring\ServiceInterface\CatalogSyndicationMappingServiceInterface;
use App\Vendoring\ValueObject\CategorySyndicationMappingProfile;
use App\Vendoring\ValueObject\CategorySyndicationPublishPackage;
use DateTimeImmutable;

final readonly class CatalogSyndicationMappingService implements CatalogSyndicationMappingServiceInterface
{
    public function __construct(
        private CategorySyndicationMappingPolicyInterface $policy,
    ) {}

    public function buildPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPublishPackageBuiltInterface
    {
        $this->policy->assertLocaleMode($request->localeMode);
        $normalizedFieldMap = $this->policy->normalizeFieldMap($request->fieldMap);
        $normalizedRequiredFields = $this->policy->normalizeRequiredFields($request->requiredFields);

        $profile = new CategorySyndicationMappingProfile(
            trim($request->destinationId),
            trim($request->version),
            $normalizedFieldMap,
            $normalizedRequiredFields,
            trim($request->localeMode),
        );

        $payload = [];
        foreach ($profile->fieldMap() as $sourceField => $targetField) {
            $payload[$targetField] = $request->categoryData[$sourceField] ?? null;
        }

        $missingRequiredFields = [];
        foreach ($profile->requiredFields() as $requiredField) {
            $mappedValue = $payload[$requiredField] ?? null;
            if (null === $mappedValue || '' === self::stringOrEmpty($mappedValue)) {
                $missingRequiredFields[] = $requiredField;
            }
        }

        $package = new CategorySyndicationPublishPackage(
            trim($request->packageId),
            $profile->destinationId(),
            trim($request->categoryId),
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
                'actorId' => trim($request->actorId),
                'reason' => trim($request->reason),
            ],
            new DateTimeImmutable('now'),
        );
    }

    private static function stringOrEmpty(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
