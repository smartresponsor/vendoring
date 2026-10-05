<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\Vendor\VendorMediaEntity;

interface VendorMediaRepositoryInterface
{
    public function find(mixed $id): ?VendorMediaEntity;

    public function byId(mixed $id): ?VendorMediaEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorMediaEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorMediaEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorMediaEntity $entity, bool $flush = false): void;

    public function flush(): void;
}
