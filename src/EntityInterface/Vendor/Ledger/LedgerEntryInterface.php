<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\EntityInterface\Vendor\Ledger;

interface LedgerEntryInterface
{

    public function __construct(public string $id, public string $tenantId, public string $debitAccount, public string $creditAccount, public float $amount, public string $currency, public string $referenceType, public string $referenceId, public ?string $vendorId, public string $createdAt);
}
