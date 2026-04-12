<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\DTO\CatalogSyndication\CatalogSyndicationPublishPackageRequestDTO;
use App\Event\CategorySyndicationPolicyAwarePackageGated;
use App\EventInterface\CategorySyndicationPolicyAwarePackageGatedInterface;
use App\PolicyInterface\CategorySyndicationPolicyAwarePackageGatePolicyInterface;
use App\ServiceInterface\CatalogDestinationMediaPolicyPreferenceServiceInterface;
use App\ServiceInterface\CatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\ServiceInterface\CatalogSyndicationPolicyAwarePackageGateServiceInterface;
use DateTimeImmutable;

final readonly class CatalogSyndicationPolicyAwarePackageGateService implements CatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    public function __construct(
        private CatalogSyndicationFallbackAwarePackageGateServiceInterface $fallbackAwareGateService,
        private CatalogDestinationMediaPolicyPreferenceServiceInterface $destinationMediaPolicyPreferenceService,
        private CategorySyndicationPolicyAwarePackageGatePolicyInterface $policy,
    ) {}

    public function buildGatedPublishPackage(CatalogSyndicationPublishPackageRequestDTO $request): CategorySyndicationPolicyAwarePackageGatedInterface
    {
        $fallbackGatePayload = $this->fallbackAwareGateService->buildGatedPublishPackage($request)->payload();
        $policyPayload = $this->destinationMediaPolicyPreferenceService->evaluate(
            $request->destinationId,
            $request->categoryId,
            $request->actorId,
            $request->reason,
        )->payload();

        $report = $this->policy->buildReport(
            self::stringList($fallbackGatePayload['packageMissingRequiredFields'] ?? null),
            self::arrayMap($policyPayload),
            self::arrayMap($fallbackGatePayload),
        );

        return new CategorySyndicationPolicyAwarePackageGated(
            [
                'packageId' => trim($request->packageId),
                'destinationId' => trim($request->destinationId),
                'categoryId' => trim($request->categoryId),
                'version' => trim($request->version),
                'localeMode' => trim($request->localeMode),
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
}
