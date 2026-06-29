<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;

interface VendorLedgerRepositoryInterface
{
    public function find(mixed $id): ?object;

    /** @param array<string, mixed> $criteria */
    public function findOneBy(array $criteria): ?object;

    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<object>
     */
    public function findBy(array $criteria): array;

    public function save(object $entity, bool $flush = false): void;

    public function insert(VendorLedgerEntity $entry): void;

    /** @return list<VendorLedgerEntity> */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array;

    public function sumByAccount(VendorLedgerAccountSumCriteriaDTO $criteria): float;

    /** @return list<object> */
    public function balancesForVendor(string $vendorId): array;
}
