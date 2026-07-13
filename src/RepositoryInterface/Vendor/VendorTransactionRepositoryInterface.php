<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;

interface VendorTransactionRepositoryInterface
{
    public function find(mixed $id): ?VendorTransactionEntity;

    public function byId(mixed $id): ?VendorTransactionEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorTransactionEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorTransactionEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /** @return list<VendorTransactionEntity> */
    public function findByVendorId(string $vendorId): array;

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransactionEntity;

    public function save(VendorTransactionEntity $entity, bool $flush = false): void;

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool;
}
