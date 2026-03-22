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
final class VendorFinanceRuntimeView
{
    /**
     * @param array<string,mixed>|null $ownership
     * @param array<string,mixed>      $metricOverview
     * @param array<string,mixed>|null $payoutAccount
     * @param array<string,mixed>|null $statement
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $vendorId,
        private readonly string $currency,
        private readonly ?array $ownership,
        private readonly array $metricOverview,
        private readonly ?array $payoutAccount,
        private readonly ?array $statement,
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
