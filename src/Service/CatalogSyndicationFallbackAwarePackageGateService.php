<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\Event\CategorySyndicationFallbackAwarePackageGated;
use App\EventInterface\CategorySyndicationFallbackAwarePackageGatedInterface;
use App\PolicyInterface\CategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\ServiceInterface\CatalogDestinationMediaFallbackServiceInterface;
use App\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
use App\ServiceInterface\CatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\ServiceInterface\CatalogSyndicationMappingServiceInterface;
use DateTimeImmutable;

final readonly class CatalogSyndicationFallbackAwarePackageGateService implements CatalogSyndicationFallbackAwarePackageGateServiceInterface
{
    public function __construct(
        private CatalogSyndicationMappingServiceInterface $mappingService,
        private CatalogDestinationMediaReadinessServiceInterface $destinationMediaReadinessService,
        private CatalogDestinationMediaFallbackServiceInterface $destinationMediaFallbackService,
        private CategorySyndicationFallbackAwarePackageGatePolicyInterface $policy,
    ) {
    }

    public function buildGatedPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationFallbackAwarePackageGatedInterface
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

        $report = $this->policy->buildReport(new \App\DTO\CatalogSyndication\CategorySyndicationFallbackAwarePackageGateReportInputDTO(
            packageMissingRequiredFields: self::stringList($packagePayload['missingRequiredFields'] ?? null),
            strictMediaRequiredMissing: self::stringList($strictMedia['requiredMissing'] ?? null),
            fallbackMediaRequiredMissing: self::stringList($fallbackMedia['requiredMissing'] ?? null),
            warnings: array_values(array_merge(
                self::stringList($strictMedia['warnings'] ?? null),
                self::stringList($fallbackMedia['warnings'] ?? null),
            )),
            strictChecks: self::boolMap($strictMedia['checks'] ?? null),
            fallbackChecks: self::boolMap($fallbackMedia['checks'] ?? null),
            exactMatchedBindingIds: self::stringList($fallbackMedia['exactMatchedBindingIds'] ?? null),
            fallbackMatchedBindingIds: self::stringList($fallbackMedia['fallbackMatchedBindingIds'] ?? null),
        ));

        return new CategorySyndicationFallbackAwarePackageGated(
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
        return is_array($value) ? $value : [];
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
