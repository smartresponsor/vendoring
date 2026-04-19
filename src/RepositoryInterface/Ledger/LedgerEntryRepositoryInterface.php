<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Ledger;

use App\Vendoring\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;
use Doctrine\DBAL\Exception;

interface LedgerEntryRepositoryInterface
{
    /** @throws Exception */
    public function insert(LedgerEntry $entry): void;

    /**
     * @return list<LedgerEntry>
     * @throws Exception
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array;

    /** @throws Exception */
    public function sumByAccount(LedgerAccountSumCriteriaDTO $criteria): float;

    /**
     * @return list<object{currency:string,balanceCents:int}>
     * @throws Exception
     */
    public function balancesForVendor(string $vendorId): array;
}
