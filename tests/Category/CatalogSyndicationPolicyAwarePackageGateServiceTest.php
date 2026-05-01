<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Vendoring\Tests\Category;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationPublishPackageRequestDTO;
use App\Vendoring\Policy\Vendor\VendorCategorySyndicationPolicyAwarePackageGatePolicy;
use App\Vendoring\Service\Syndication\VendorCatalogSyndicationPolicyAwarePackageGateService;
use App\Vendoring\ServiceInterface\Media\VendorCatalogDestinationMediaPolicyPreferenceServiceInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationPolicyAwarePackageGateServiceTest extends TestCase
{
    public function testBuildGatedPublishPackageResolvesPublishabilityViaPolicy(): void
    {
        $fallbackAwareGateService = new class implements VendorCatalogSyndicationFallbackAwarePackageGateServiceInterface {
            public function buildGatedPublishPackage(VendorCatalogSyndicationPublishPackageRequestDTO $request): \App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEventInterface
            {
                return new \App\Vendoring\Event\Vendor\VendorCategorySyndicationFallbackAwarePackageGatedEvent([
                    'packageId' => $request->packageId,
                    'destinationId' => $request->destinationId,
                    'categoryId' => $request->categoryId,
                    'version' => $request->version,
                    'localeMode' => $request->localeMode,
                    'payload' => ['slug' => 'chairs'],
                    'fieldMap' => $request->fieldMap,
                    'requiredFields' => $request->requiredFields,
                    'packageMissingRequiredFields' => [],
                    'warnings' => ['package_publishable_via_fallback_only'],
                    'checks' => ['fallbackPackageGatePublishable' => true],
                    'exactMatchedBindingIds' => ['m1'],
                    'fallbackMatchedBindingIds' => ['m2'],
                ], new \DateTimeImmutable());
            }
        };

        $preferenceService = new class implements VendorCatalogDestinationMediaPolicyPreferenceServiceInterface {
            public function evaluate(string $destinationId, string $categoryId, string $actorId, string $reason): \App\Vendoring\EventInterface\Vendor\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface
            {
                return new \App\Vendoring\Event\Vendor\VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent([
                    'destinationId' => $destinationId,
                    'categoryId' => $categoryId,
                    'mediaPolicyMode' => 'allow_fallback',
                    'strictPublishable' => false,
                    'fallbackPublishable' => true,
                    'resolvedPublishable' => true,
                    'fallbackUsed' => true,
                    'requiredMissing' => [],
                    'warnings' => ['destination_media_policy_preferred_exact_fallback_used'],
                    'checks' => ['resolvedPublishable' => true],
                    'actorId' => $actorId,
                    'reason' => $reason,
                ], new \DateTimeImmutable());
            }
        };

        $service = new VendorCatalogSyndicationPolicyAwarePackageGateService(
            $fallbackAwareGateService,
            $preferenceService,
            new VendorCategorySyndicationPolicyAwarePackageGatePolicy(),
        );

        $event = $service->buildGatedPublishPackage(new VendorCatalogSyndicationPublishPackageRequestDTO('pkg-1', 'dst-1', 'cat-1', 'v1', 'per_locale', ['slug' => 'chairs'], ['slug' => 'slug'], ['slug'], 'actor-1', 'test'));
        $payload = $event->payload();

        self::assertSame('allow_fallback', $payload['mediaPolicyMode'] ?? null);
        self::assertTrue((bool) ($payload['resolvedPublishable'] ?? false));
        self::assertTrue((bool) ($payload['fallbackUsed'] ?? false));

        $warnings = $payload['warnings'] ?? [];
        self::assertIsArray($warnings);
        self::assertContains('package_publishable_by_destination_media_policy_fallback', $warnings);
    }
}
