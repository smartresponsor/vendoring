<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Ledger;

use App\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Entity\Ledger\LedgerEntry;

interface LedgerEntryRepositoryInterface
{
    public function insert(LedgerEntry $entry): void;

    /**
     * @return list<LedgerEntry>
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array;

    public function sumByAccount(LedgerAccountSumCriteriaDTO $criteria): float;

    /**
     * @return list<object{currency:string,balanceCents:int}>
     */
    public function balancesForVendor(string $vendorId): array;
}
