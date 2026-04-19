<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Vendoring\Tests\Category;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationGovernanceTrailRequestDTO;
use App\Vendoring\Policy\CategorySyndicationGovernanceTrailPolicy;
use App\Vendoring\Service\CatalogSyndicationGovernanceTrailService;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationGovernanceTrailServiceTest extends TestCase
{
    public function testRecordTrailIncludesPolicyDeliveryAndHistorySignals(): void
    {
        $service = new CatalogSyndicationGovernanceTrailService(new CategorySyndicationGovernanceTrailPolicy());

        $event = $service->recordTrail(new CatalogSyndicationGovernanceTrailRequestDTO(
            [
                'destinationId' => 'dst-1',
                'categoryId' => 'cat-1',
                'mediaPolicyMode' => 'prefer_exact_warn',
                'strictPublishable' => false,
                'fallbackPublishable' => true,
                'resolvedPublishable' => true,
                'fallbackUsed' => true,
                'warnings' => ['package_publishable_by_destination_media_policy_fallback'],
            ],
            [
                'destinationId' => 'dst-1',
                'categoryId' => 'cat-1',
                'status' => 'retry_scheduled',
                'retryable' => true,
            ],
            [
                'destinationId' => 'dst-1',
                'totalRecords' => 4,
                'deliveredCount' => 1,
                'failedCount' => 2,
                'pendingCount' => 0,
                'retryScheduledCount' => 1,
                'skippedCount' => 0,
            ],
            [
                'scheduledRetries' => 1,
            ],
            'actor-1',
            'test',
        ));

        $payload = $event->payload();

        self::assertSame('prefer_exact_warn', $payload['mediaPolicyMode'] ?? null);
        self::assertTrue((bool) ($payload['resolvedPublishable'] ?? false));
        self::assertTrue((bool) ($payload['fallbackUsed'] ?? false));
        self::assertTrue((bool) ($payload['retryScheduled'] ?? false));

        $historyCounts = $payload['historyCounts'] ?? [];
        self::assertIsArray($historyCounts);
        self::assertSame(2, $historyCounts['failedCount'] ?? null);

        $warnings = $payload['warnings'] ?? [];
        self::assertIsArray($warnings);
        self::assertContains('governance_trail_fallback_used', $warnings);

        $checks = $payload['checks'] ?? [];
        self::assertIsArray($checks);
        self::assertTrue((bool) ($checks['governanceTrailHasFailures'] ?? false));
    }
}
