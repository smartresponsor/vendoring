<?php

declare(strict_types=1);

namespace App\Vendoring\Projection;

/**
 * Aggregated vendor-local runtime status view spanning ownership, finance,
 * statement delivery, and external integrations without loading external aggregates.
 */
final readonly class VendorRuntimeStatusView
{
    /**
     * @param array<string, mixed>|null $ownership
     * @param array<string, mixed>      $finance
     * @param array<string, mixed>      $statementDelivery
     * @param array<string, mixed>      $externalIntegration
     * @param array<string, bool>       $surfaceStatus
     */
    public function __construct(
        private string $tenantId,
        private string $vendorId,
        private string $currency,
        private ?array $ownership,
        private array  $finance,
        private array  $statementDelivery,
        private array  $externalIntegration,
        private array  $surfaceStatus,
        private string $generatedAt,
    ) {}

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   currency:string,
     *   ownership:array<string,mixed>|null,
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
            'finance' => $this->finance,
            'statementDelivery' => $this->statementDelivery,
            'externalIntegration' => $this->externalIntegration,
            'surfaceStatus' => $this->surfaceStatus,
            'generatedAt' => $this->generatedAt,
        ];
    }
}
