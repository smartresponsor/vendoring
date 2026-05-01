<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\DTO\CatalogSyndication\VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\Vendoring\Event\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEventInterface;
use App\Vendoring\PolicyInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaFallbackServiceInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaReadinessServiceInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationMappingServiceInterface;
use DateTimeImmutable;

final readonly class VendorCatalogSyndicationFallbackAwarePackageGateService implements VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface
{
    public function __construct(
        private VendorCatalogSyndicationMappingServiceInterface $mappingService,
        private VendorCatalogDestinationMediaReadinessServiceInterface $destinationMediaReadinessService,
        private VendorCatalogDestinationMediaFallbackServiceInterface $destinationMediaFallbackService,
        private VendorCategorySyndicationFallbackAwarePackageGatePolicyInterface $policy,
    ) {}

    public function buildGatedPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): VendorCategorySyndicationFallbackAwarePackageGatedEventInterface
    {
        $packageBuilt = $this->mappingService->buildPublishPackage($request);
        $packagePayload = $packageBuilt->payload();

        $strictMedia = $this->destinationMediaReadinessService->evaluate(
            $request->destinationId,
            $request->categoryId,
            $request->actorId,
            $request->reason,
        )->payload();
        $fallbackMedia = $this->destinationMediaFallbackService->evaluate(
            $request->destinationId,
            $request->categoryId,
            $request->actorId,
            $request->reason,
        )->payload();

        $report = $this->policy->buildReport(new VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO(
            packageMissingRequiredFields: self::stringList($packagePayload['missingRequiredFields'] ?? null),
            strictMediaRequiredMissing: self::stringList($strictMedia['requiredMissing'] ?? null),
            fallbackMediaRequiredMissing: self::stringList($fallbackMedia['requiredMissing'] ?? null),
            warnings: array_merge(
                self::stringList($strictMedia['warnings'] ?? null),
                self::stringList($fallbackMedia['warnings'] ?? null),
            ),
            strictChecks: self::boolMap($strictMedia['checks'] ?? null),
            fallbackChecks: self::boolMap($fallbackMedia['checks'] ?? null),
            exactMatchedBindingIds: self::stringList($fallbackMedia['exactMatchedBindingIds'] ?? null),
            fallbackMatchedBindingIds: self::stringList($fallbackMedia['fallbackMatchedBindingIds'] ?? null),
        ));

        return new VendorCategorySyndicationFallbackAwarePackageGatedEvent(
            [
                'packageId' => trim($request->packageId),
                'destinationId' => trim($request->destinationId),
                'categoryId' => trim($request->categoryId),
                'version' => trim($request->version),
                'localeMode' => trim($request->localeMode),
                'payload' => self::arrayMap($packagePayload['payload'] ?? null),
                'fieldMap' => self::stringMap($packagePayload['fieldMap'] ?? null),
                'requiredFields' => self::stringList($packagePayload['requiredFields'] ?? null),
                'packageMissingRequiredFields' => $report->packageMissingRequiredFields(),
                'strictMediaRequiredMissing' => $report->strictMediaRequiredMissing(),
                'fallbackMediaRequiredMissing' => $report->fallbackMediaRequiredMissing(),
                'warnings' => $report->warnings(),
                'checks' => $report->checks(),
                'exactMatchedBindingIds' => $report->exactMatchedBindingIds(),
                'fallbackMatchedBindingIds' => $report->fallbackMatchedBindingIds(),
                'strictPublishable' => $report->strictPublishable(),
                'fallbackPublishable' => $report->fallbackPublishable(),
                'actorId' => trim($request->actorId),
                'reason' => trim($request->reason),
            ],
            new DateTimeImmutable(),
        );
    }

    /** @return array<string, mixed> */
    private static function arrayMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_filter($value, static function ($key): bool {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /** @return array<string, string> */
    private static function stringMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && is_scalar($item)) {
                $result[$key] = (string) $item;
            }
        }

        return $result;
    }

    /** @return list<string> */
    private static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }

    /** @return array<string, bool> */
    private static function boolMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = (bool) $item;
            }
        }

        return $result;
    }
}
