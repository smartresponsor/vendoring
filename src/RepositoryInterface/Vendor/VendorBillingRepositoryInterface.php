<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;

interface VendorBillingRepositoryInterface
{
    public function find(mixed $id): ?VendorBillingEntity;

    public function byId(mixed $id): ?VendorBillingEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorBillingEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorBillingEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorBillingEntity $entity, bool $flush = false): void;

    /** @return list<VendorBillingEntity> */
    public function findAll(): array;
}
