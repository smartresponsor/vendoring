<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Vendor-local statement delivery runtime summary.
 *
 * Keeps ownership/access semantics adjacent to statement export and recipient
 * delivery surfaces without pulling an external User aggregate.
 */
final readonly class VendorStatementDeliveryRuntimeView
{
    /**
     * @param array<string,mixed>|null  $ownership
     * @param array<string,mixed>       $statement
     * @param array<string,mixed>|null  $export
     * @param list<array<string,mixed>> $recipients
     */
    public function __construct(
        private string $tenantId,
        private string $vendorId,
        private string $currency,
        private ?array $ownership,
        private array  $statement,
        private ?array $export,
        private array  $recipients,
    ) {}

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   currency:string,
     *   ownership:array<string,mixed>|null,
     *   statement:array<string,mixed>,
     *   export:array<string,mixed>|null,
     *   recipients:list<array<string,mixed>>
     * }
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'vendorId' => $this->vendorId,
            'currency' => $this->currency,
            'ownership' => $this->ownership,
            'statement' => $this->statement,
            'export' => $this->export,
            'recipients' => $this->recipients,
        ];
    }
}
