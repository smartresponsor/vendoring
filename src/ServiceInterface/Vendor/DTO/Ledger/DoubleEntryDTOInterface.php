<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Ledger;

interface DoubleEntryDTOInterface
{

    public function __construct(public string $tenantId, public string $debitAccount, public string $creditAccount, public float $amount, public string $currency, public string $referenceType, public string $referenceId, public ?string $vendorId = null, public ?string $occurredAt = null);
}
