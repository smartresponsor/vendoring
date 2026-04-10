<?php

declare(strict_types=1);

namespace App\DTO\Ledger;

final readonly class LedgerAccountSumCriteriaDTO
{
    public function __construct(
        public string $tenantId,
        public string $accountCode,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $vendorId = null,
        public ?string $currency = null,
    ) {
    }
}
