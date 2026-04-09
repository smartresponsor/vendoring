<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Ledger;

use App\Entity\Ledger\LedgerEntry;

/**
 * Persistence contract for ledger entry repository records.
 */
interface LedgerEntryRepositoryInterface
{
    /**
     * Executes the insert operation for this runtime surface.
     */
    public function insert(LedgerEntry $entry): void;

    /**
     * @return list<LedgerEntry>
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array;

    /**
     * Executes the sum by account operation for this runtime surface.
     */
    public function sumByAccount(string $tenantId, string $accountCode, ?string $from = null, ?string $to = null, ?string $vendorId = null, ?string $currency = null): float;

    /**
     * @return list<object{currency:string,balanceCents:int}>
     */
    public function balancesForVendor(string $vendorId): array;
}
