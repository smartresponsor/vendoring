<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;

interface VendorPayoutRepositoryInterface
{
    public function find(mixed $id): ?VendorPayoutEntity;

    public function byId(mixed $id): ?VendorPayoutEntity;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?VendorPayoutEntity;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<VendorPayoutEntity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(VendorPayoutEntity $entity, bool $flush = false): void;

    public function insert(VendorPayoutEntity $payout): void;

    /** @param array<string, mixed> $meta */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void;

    /** @param array<string, mixed> $meta */
    public function markFailed(string $id, string $processedAt, array $meta = []): void;
}
