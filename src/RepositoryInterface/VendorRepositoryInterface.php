<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

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

    /**
     * @return list<array{id: int, brandName: string, ownerUserId: int|null}>
     */
    public function findIndexRows(): array;

    public function findOneForUserId(int $userId): ?VendorEntity;

    public function save(VendorEntity $entity, bool $flush = false): void;

    public function remove(VendorEntity $entity, bool $flush = false): void;

    /** @return list<array<string, mixed>> */
    public function findAccessBootstrapRows(): array;

    /** @return list<array<string, mixed>> */
    public function findPublishedRetailServiceRows(): array;

    public function seedDeterministicVendor(int $id, string $brandName, int $ownerUserId): void;
}
