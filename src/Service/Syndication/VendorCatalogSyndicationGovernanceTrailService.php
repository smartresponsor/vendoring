<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Syndication;

use App\Vendoring\DTO\CatalogSyndication\VendorCatalogSyndicationGovernanceTrailRequestDTO;
use App\Vendoring\Event\Vendor\VendorCategorySyndicationGovernanceTrailRecordedEvent;
use App\Vendoring\EventInterface\Vendor\VendorCategorySyndicationGovernanceTrailRecordedEventInterface;
use App\Vendoring\PolicyInterface\Vendor\VendorCategorySyndicationGovernanceTrailPolicyInterface;
use App\Vendoring\ServiceInterface\Syndication\VendorCatalogSyndicationGovernanceTrailServiceInterface;
use DateTimeImmutable;

final readonly class VendorCatalogSyndicationGovernanceTrailService implements VendorCatalogSyndicationGovernanceTrailServiceInterface
{
    public function __construct(
        private VendorCategorySyndicationGovernanceTrailPolicyInterface $policy,
    ) {}

    public function recordTrail(VendorCatalogSyndicationGovernanceTrailRequestDTO $request): VendorCategorySyndicationGovernanceTrailRecordedEventInterface
    {
        $report = $this->policy->buildReport(
            $request->policyAwarePayload,
            $request->deliveryPayload,
            $request->historyPayload,
            $request->recoveryPayload,
        );

        return new VendorCategorySyndicationGovernanceTrailRecordedEvent(
            [
                'destinationId' => $report->destinationId(),
                'categoryId' => $report->categoryId(),
                'mediaPolicyMode' => $report->mediaPolicyMode(),
                'strictPublishable' => $report->strictPublishable(),
                'fallbackPublishable' => $report->fallbackPublishable(),
                'resolvedPublishable' => $report->resolvedPublishable(),
                'fallbackUsed' => $report->fallbackUsed(),
                'deliveryStatus' => $report->deliveryStatus(),
                'retryable' => $report->retryable(),
                'retryScheduled' => $report->retryScheduled(),
                'historyCounts' => $report->historyCounts(),
                'warnings' => $report->warnings(),
                'checks' => $report->checks(),
                'actorId' => trim($request->actorId),
                'reason' => trim($request->reason),
            ],
            new DateTimeImmutable(),
        );
    }
}
