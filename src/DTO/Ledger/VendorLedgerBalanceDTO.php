<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ledger;

final readonly class VendorLedgerBalanceDTO
{
    public function __construct(
        public string $currency,
        public int $balanceCents,
    ) {
    }
}
