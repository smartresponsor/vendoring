<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\Event\Vendor\VendorCategorySyndicationPublishPackageBuiltEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationPublishPackageBuiltEventInterface;
use App\Vendoring\PolicyInterface\Vendor\VendorCategorySyndicationMappingPolicyInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationMappingServiceInterface;
use App\Vendoring\ValueObject\VendorCategorySyndicationMappingProfileValueObject;
use App\Vendoring\ValueObject\VendorCategorySyndicationPublishPackageValueObject;
use DateTimeImmutable;

final readonly class VendorCatalogSyndicationMappingService implements VendorCatalogSyndicationMappingServiceInterface
{
    public function __construct(
        private VendorCategorySyndicationMappingPolicyInterface $policy,
    ) {}

    public function buildPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): VendorCategorySyndicationPublishPackageBuiltEventInterface
    {
        $this->policy->assertLocaleMode($request->localeMode);
        $normalizedFieldMap = $this->policy->normalizeFieldMap($request->fieldMap);
        $normalizedRequiredFields = $this->policy->normalizeRequiredFields($request->requiredFields);

        $profile = new VendorCategorySyndicationMappingProfileValueObject(
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

        $package = new VendorCategorySyndicationPublishPackageValueObject(
            trim($request->packageId),
            $profile->destinationId(),
            trim($request->categoryId),
            $profile->version(),
            $profile->localeMode(),
            $payload,
            $missingRequiredFields,
            [] === $missingRequiredFields,
        );

        return new VendorCategorySyndicationPublishPackageBuiltEvent(
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
