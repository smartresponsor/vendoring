<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\EventInterface\CategorySyndicationGovernanceTrailRecordedInterface;

interface CatalogSyndicationGovernanceTrailServiceInterface
{
    /**
     * @param array<string, mixed> $policyAwarePayload
     * @param array<string, mixed> $deliveryPayload
     * @param array<string, mixed> $historyPayload
     * @param array<string, mixed> $recoveryPayload
     */
    public function recordTrail(array $policyAwarePayload, array $deliveryPayload, array $historyPayload, array $recoveryPayload, string $actorId, string $reason): CategorySyndicationGovernanceTrailRecordedInterface;
}
