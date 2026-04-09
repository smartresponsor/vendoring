<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Vendor-local finance runtime summary.
 *
 * This read-side keeps ownership/access semantics adjacent to finance-facing
 * statement, metric and payout-account surfaces without pulling any external
 * User aggregate.
 */
final readonly class VendorFinanceRuntimeView
{
    /**
     * @param array<string,mixed>|null $ownership
     * @param array<string,mixed>      $metricOverview
     * @param array<string,mixed>|null $payoutAccount
     * @param array<string,mixed>|null $statement
     */
    public function __construct(
        private string $tenantId,
        private string $vendorId,
        private string $currency,
        private ?array $ownership,
        private array $metricOverview,
        private ?array $payoutAccount,
        private ?array $statement,
    ) {
    }

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   currency:string,
     *   ownership:array<string,mixed>|null,
     *   metricOverview:array<string,mixed>,
     *   payoutAccount:array<string,mixed>|null,
     *   statement:array<string,mixed>|null
     * }
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'vendorId' => $this->vendorId,
            'currency' => $this->currency,
            'ownership' => $this->ownership,
            'metricOverview' => $this->metricOverview,
            'payoutAccount' => $this->payoutAccount,
            'statement' => $this->statement,
        ];
    }
}
