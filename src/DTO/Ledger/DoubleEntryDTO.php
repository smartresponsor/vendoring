<?php

declare(strict_types=1);

namespace App\DTO\Ledger;

final class DoubleEntryDTO
{
    public function __construct(public string $tenantId, public string $debitAccount, public string $creditAccount, public float $amount, public string $currency, public string $referenceType, public string $referenceId, public ?string $vendorId = null, public ?string $occurredAt = null) {}
}
