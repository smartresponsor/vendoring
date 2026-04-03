<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Event\CategorySyndicationFallbackAwarePackageGated;
use App\EventInterface\CategorySyndicationFallbackAwarePackageGatedInterface;
use App\PolicyInterface\CategorySyndicationFallbackAwarePackageGatePolicyInterface;
use App\ServiceInterface\CatalogDestinationMediaFallbackServiceInterface;
use App\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
use App\ServiceInterface\CatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\ServiceInterface\CatalogSyndicationMappingServiceInterface;

final class CatalogSyndicationFallbackAwarePackageGateService implements CatalogSyndicationFallbackAwarePackageGateServiceInterface
{
    public function __construct(
        private readonly CatalogSyndicationMappingServiceInterface $mappingService,
        private readonly CatalogDestinationMediaReadinessServiceInterface $destinationMediaReadinessService,
        private readonly CatalogDestinationMediaFallbackServiceInterface $destinationMediaFallbackService,
        private readonly CategorySyndicationFallbackAwarePackageGatePolicyInterface $policy,
    ) {
    }

    /**
     * @param array<string, mixed> $categoryData
     * @param array<string, string> $fieldMap
     * @param list<string> $requiredFields
     */
    public function buildGatedPublishPackage(string $packageId, string $destinationId, string $categoryId, string $version, string $localeMode, array $categoryData, array $fieldMap, array $requiredFields, string $actorId, string $reason): CategorySyndicationFallbackAwarePackageGatedInterface
    {
        $packageBuilt = $this->mappingService->buildPublishPackage($packageId, $destinationId, $categoryId, $version, $localeMode, $categoryData, $fieldMap, $requiredFields, $actorId, $reason);
        $packagePayload = $packageBuilt->payload();

        $strictMedia = $this->destinationMediaReadinessService->evaluate($destinationId, $categoryId, $actorId, $reason)->payload();
        $fallbackMedia = $this->destinationMediaFallbackService->evaluate($destinationId, $categoryId, $actorId, $reason)->payload();

        $report = $this->policy->buildReport(
            self::stringList($packagePayload['missingRequiredFields'] ?? null),
            self::stringList($strictMedia['requiredMissing'] ?? null),
            self::stringList($fallbackMedia['requiredMissing'] ?? null),
            array_values(array_merge(
                self::stringList($strictMedia['warnings'] ?? null),
                self::stringList($fallbackMedia['warnings'] ?? null),
            )),
            self::boolMap($strictMedia['checks'] ?? null),
            self::boolMap($fallbackMedia['checks'] ?? null),
            self::stringList($fallbackMedia['exactMatchedBindingIds'] ?? null),
            self::stringList($fallbackMedia['fallbackMatchedBindingIds'] ?? null),
        );

        return new CategorySyndicationFallbackAwarePackageGated(
            [
                'packageId' => trim($packageId),
                'destinationId' => trim($destinationId),
                'categoryId' => trim($categoryId),
                'version' => trim($version),
                'localeMode' => trim($localeMode),
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
                'actorId' => trim($actorId),
                'reason' => trim($reason),
            ],
            new \DateTimeImmutable(),
        );
    }

    /**
     * @param mixed $value
     *
     * @return array<string, mixed>
     */
    private static function arrayMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param mixed $value
     *
     * @return array<string, string>
     */
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

    /**
     * @param mixed $value
     *
     * @return list<string>
     */
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

    /**
     * @param mixed $value
     *
     * @return array<string, bool>
     */
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
