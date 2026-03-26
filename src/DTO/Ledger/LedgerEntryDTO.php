<?php

declare(strict_types=1);

namespace App\DTO\Ledger;

final class LedgerEntryDTO
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        public string $type,
        public string $entityId,
        public string $sagaId,
        public string $vendorId,
        public int $amountCents,
        public string $currency,
        public string $direction,
        public array $meta = [],
        public string $tenantId = 'default',
        public ?string $occurredAt = null,
    ) {
    }
}
