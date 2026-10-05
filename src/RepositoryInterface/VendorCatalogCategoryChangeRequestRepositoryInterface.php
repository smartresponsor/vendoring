<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryChangeRequestEntity;

interface VendorCatalogCategoryChangeRequestRepositoryInterface
{
    public function find(mixed $id): ?VendorCatalogCategoryChangeRequestEntity;

    public function byId(mixed $id): ?VendorCatalogCategoryChangeRequestEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorCatalogCategoryChangeRequestEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorCatalogCategoryChangeRequestEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorCatalogCategoryChangeRequestEntity $entity, bool $flush = false): void;
}
