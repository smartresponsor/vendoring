<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\DTO\CatalogSyndication\CatalogSyndicationGovernanceTrailRequestDTO;
use App\Vendoring\Event\CategorySyndicationGovernanceTrailRecorded;
use App\Vendoring\EventInterface\CategorySyndicationGovernanceTrailRecordedInterface;
use App\Vendoring\PolicyInterface\CategorySyndicationGovernanceTrailPolicyInterface;
use App\Vendoring\ServiceInterface\CatalogSyndicationGovernanceTrailServiceInterface;
use DateTimeImmutable;

final readonly class CatalogSyndicationGovernanceTrailService implements CatalogSyndicationGovernanceTrailServiceInterface
{
    public function __construct(
        private CategorySyndicationGovernanceTrailPolicyInterface $policy,
    ) {}

    public function recordTrail(CatalogSyndicationGovernanceTrailRequestDTO $request): CategorySyndicationGovernanceTrailRecordedInterface
    {
        $report = $this->policy->buildReport(
            $request->policyAwarePayload,
            $request->deliveryPayload,
            $request->historyPayload,
            $request->recoveryPayload,
        );

        return new CategorySyndicationGovernanceTrailRecorded(
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
