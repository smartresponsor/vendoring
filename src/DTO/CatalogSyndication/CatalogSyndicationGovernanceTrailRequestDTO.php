<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\CatalogSyndication;

final readonly class CatalogSyndicationGovernanceTrailRequestDTO
{
    /**
     * @param array<string, mixed> $policyAwarePayload
     * @param array<string, mixed> $deliveryPayload
     * @param array<string, mixed> $historyPayload
     * @param array<string, mixed> $recoveryPayload
     */
    public function __construct(
        public array $policyAwarePayload,
        public array $deliveryPayload,
        public array $historyPayload,
        public array $recoveryPayload,
        public string $actorId,
        public string $reason,
    ) {}
}
