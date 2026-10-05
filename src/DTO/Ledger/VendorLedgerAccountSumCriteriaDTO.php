<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ledger;

final readonly class VendorLedgerAccountSumCriteriaDTO
{
    public function __construct(
        public string $vendorId,
        public string $accountCode,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $currency = null,
    ) {
    }
}
