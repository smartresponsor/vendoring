<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Event\CategorySyndicationGovernanceTrailRecorded;
use App\EventInterface\CategorySyndicationGovernanceTrailRecordedInterface;
use App\PolicyInterface\CategorySyndicationGovernanceTrailPolicyInterface;
use App\ServiceInterface\CatalogSyndicationGovernanceTrailServiceInterface;

final class CatalogSyndicationGovernanceTrailService implements CatalogSyndicationGovernanceTrailServiceInterface
{
    public function __construct(
        private readonly CategorySyndicationGovernanceTrailPolicyInterface $policy,
    ) {
    }

    /**
     * @param array<string, mixed> $policyAwarePayload
     * @param array<string, mixed> $deliveryPayload
     * @param array<string, mixed> $historyPayload
     * @param array<string, mixed> $recoveryPayload
     */
    public function recordTrail(array $policyAwarePayload, array $deliveryPayload, array $historyPayload, array $recoveryPayload, string $actorId, string $reason): CategorySyndicationGovernanceTrailRecordedInterface
    {
        $report = $this->policy->buildReport($policyAwarePayload, $deliveryPayload, $historyPayload, $recoveryPayload);

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
                'actorId' => trim($actorId),
                'reason' => trim($reason),
            ],
            new \DateTimeImmutable(),
        );
    }
}
