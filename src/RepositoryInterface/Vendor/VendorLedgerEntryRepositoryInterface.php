<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use Doctrine\DBAL\Exception;

interface VendorLedgerEntryRepositoryInterface
{
    /** @throws Exception */
    public function insert(VendorLedgerEntryEntity $entry): void;

    /**
     * @return list<VendorLedgerEntryEntity>
     * @throws Exception
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array;

    /** @throws Exception */
    public function sumByAccount(VendorLedgerAccountSumCriteriaDTO $criteria): float;

    /**
     * @return list<object{currency:string,balanceCents:int}>
     * @throws Exception
     */
    public function balancesForVendor(string $vendorId): array;
}
