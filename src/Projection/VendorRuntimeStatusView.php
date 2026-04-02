<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Aggregated vendor-local runtime status view spanning ownership, finance,
 * statement delivery, and external integrations without loading external aggregates.
 */
final class VendorRuntimeStatusView
{
    /**
     * @param array<string, mixed>|null $ownership
     * @param array<string, mixed>|null $profile
     * @param array<string, mixed>      $finance
     * @param array<string, mixed>      $statementDelivery
     * @param array<string, mixed>      $externalIntegration
     * @param array<string, bool>       $surfaceStatus
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $vendorId,
        private readonly string $currency,
        private readonly ?array $ownership,
        private readonly ?array $profile,
        private readonly array $finance,
        private readonly array $statementDelivery,
        private readonly array $externalIntegration,
        private readonly array $surfaceStatus,
        private readonly string $generatedAt,
    ) {
    }

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   currency:string,
     *   ownership:array<string,mixed>|null,
     *   profile:array<string,mixed>|null,
     *   finance:array<string,mixed>,
     *   statementDelivery:array<string,mixed>,
     *   externalIntegration:array<string,mixed>,
     *   surfaceStatus:array<string,bool>,
     *   generatedAt:string
     * }
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'vendorId' => $this->vendorId,
            'currency' => $this->currency,
            'ownership' => $this->ownership,
            'profile' => $this->profile,
            'finance' => $this->finance,
            'statementDelivery' => $this->statementDelivery,
            'externalIntegration' => $this->externalIntegration,
            'surfaceStatus' => $this->surfaceStatus,
            'generatedAt' => $this->generatedAt,
        ];
    }
}
