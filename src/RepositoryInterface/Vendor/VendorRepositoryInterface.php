<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorRepositoryInterface
{
    public function find(mixed $id): ?VendorEntity;

    public function byId(mixed $id): ?VendorEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorEntity $entity, bool $flush = false): void;
}
