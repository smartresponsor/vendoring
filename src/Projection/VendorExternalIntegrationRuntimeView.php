<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Vendor-local runtime summary for CRM, webhook and payout-provider seams.
 *
 * Keeps neighboring integration surfaces readable without pulling external
 * aggregates or executing side effects against remote systems.
 */
final readonly class VendorExternalIntegrationRuntimeView
{
    /**
     * @param array<string,mixed>|null $ownership
     * @param array<string,mixed>      $crm
     * @param array<string,mixed>      $webhooks
     * @param array<string,mixed>      $payoutBridge
     * @param list<string>             $surfaces
     */
    public function __construct(
        private string $tenantId,
        private string $vendorId,
        private ?array $ownership,
        private array  $crm,
        private array  $webhooks,
        private array  $payoutBridge,
        private array  $surfaces,
    ) {
    }

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   ownership:array<string,mixed>|null,
     *   crm:array<string,mixed>,
     *   webhooks:array<string,mixed>,
     *   payoutBridge:array<string,mixed>,
     *   surfaces:list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'vendorId' => $this->vendorId,
            'ownership' => $this->ownership,
            'crm' => $this->crm,
            'webhooks' => $this->webhooks,
            'payoutBridge' => $this->payoutBridge,
            'surfaces' => $this->surfaces,
        ];
    }
}
