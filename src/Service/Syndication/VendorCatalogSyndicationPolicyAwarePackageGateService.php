<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\Event\Vendor\VendorCategorySyndicationPolicyAwarePackageGatedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationPolicyAwarePackageGatedEventInterface;
use App\Vendoring\PolicyInterface\Vendor\VendorCategorySyndicationPolicyAwarePackageGatePolicyInterface;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaPolicyPreferenceServiceInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationPolicyAwarePackageGateServiceInterface;
use DateTimeImmutable;

final readonly class VendorCatalogSyndicationPolicyAwarePackageGateService implements VendorCatalogSyndicationPolicyAwarePackageGateServiceInterface
{
    public function __construct(
        private VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface $fallbackAwareGateService,
        private VendorCatalogDestinationMediaPolicyPreferenceServiceInterface $destinationMediaPolicyPreferenceService,
        private VendorCategorySyndicationPolicyAwarePackageGatePolicyInterface $policy,
    ) {}

    public function buildGatedPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): VendorCategorySyndicationPolicyAwarePackageGatedEventInterface
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

        return new VendorCategorySyndicationPolicyAwarePackageGatedEvent(
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
}
