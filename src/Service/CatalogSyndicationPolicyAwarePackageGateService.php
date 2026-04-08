<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Event\CategorySyndicationPolicyAwarePackageGated;
use App\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;
use App\PolicyInterface\CategorySyndicationPolicyAwarePackageGatePolicyInterface;
use App\ServiceInterface\CatalogDestinationMediaPolicyPreferenceServiceInterface;
use App\ServiceInterface\CatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\ServiceInterface\CatalogSyndicationPolicyAwarePackageGateServiceInterface;

/**
 * Application service for catalog syndication policy aware package gate operations.
 */
final class CatalogSyndicationPolicyAwarePackageGateService implements CatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    public function __construct(
        private readonly CatalogSyndicationFallbackAwarePackageGateServiceInterface $fallbackAwareGateService,
        private readonly CatalogDestinationMediaPolicyPreferenceServiceInterface $destinationMediaPolicyPreferenceService,
        private readonly CategorySyndicationPolicyAwarePackageGatePolicyInterface $policy,
    ) {
    }

    /**
     * @param array<string, mixed> $categoryData
     * @param array<string, string> $fieldMap
     * @param list<string> $requiredFields
     */
    public function buildGatedPublishPackage(string $packageId, string $destinationId, string $categoryId, string $version, string $localeMode, array $categoryData, array $fieldMap, array $requiredFields, string $actorId, string $reason): CategorySyndicationPolicyAwarePackageGatedInterface
    {
        $fallbackGatePayload = $this->fallbackAwareGateService->buildGatedPublishPackage($packageId, $destinationId, $categoryId, $version, $localeMode, $categoryData, $fieldMap, $requiredFields, $actorId, $reason)->payload();
        $policyPayload = $this->destinationMediaPolicyPreferenceService->evaluate($destinationId, $categoryId, $actorId, $reason)->payload();

        $report = $this->policy->buildReport(
            self::stringList($fallbackGatePayload['packageMissingRequiredFields'] ?? null),
            self::arrayMap($policyPayload),
            self::arrayMap($fallbackGatePayload),
        );

        return new CategorySyndicationPolicyAwarePackageGated(
            [
                'packageId' => trim($packageId),
                'destinationId' => trim($destinationId),
                'categoryId' => trim($categoryId),
                'version' => trim($version),
                'localeMode' => trim($localeMode),
                'payload' => self::arrayMap($fallbackGatePayload['payload'] ?? null),
                'fieldMap' => self::stringMap($fallbackGatePayload['fieldMap'] ?? null),
                'requiredFields' => self::stringList($fallbackGatePayload['requiredFields'] ?? null),
                'mediaPolicyMode' => $report->mediaPolicyMode(),
                'packageMissingRequiredFields' => $report->packageMissingRequiredFields(),
                'requiredMissing' => $report->requiredMissing(),
                'warnings' => $report->warnings(),
                'checks' => $report->checks(),
                'exactMatchedBindingIds' => $report->exactMatchedBindingIds(),
                'fallbackMatchedBindingIds' => $report->fallbackMatchedBindingIds(),
                'strictPublishable' => $report->strictPublishable(),
                'fallbackPublishable' => $report->fallbackPublishable(),
                'resolvedPublishable' => $report->resolvedPublishable(),
                'fallbackUsed' => $report->fallbackUsed(),
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
}
