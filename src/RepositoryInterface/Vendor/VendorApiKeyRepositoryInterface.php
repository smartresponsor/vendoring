<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;

interface VendorApiKeyRepositoryInterface
{
    public function find(mixed $id): ?VendorApiKeyEntity;

    public function byId(mixed $id): ?VendorApiKeyEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorApiKeyEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorApiKeyEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorApiKeyEntity $entity, bool $flush = false): void;

    public function findActiveByTokenHash(string $tokenHash): ?VendorApiKeyEntity;
}
