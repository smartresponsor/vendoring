<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

interface VendorTransactionRepositoryInterface
{
    public function find(mixed $id): ?object;

    public function byId(mixed $id): ?object;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?object;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<object>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /** @return list<object> */
    public function findByVendorId(string $vendorId): array;

    public function save(object $entity, bool $flush = false): void;

    /**
     * Returns true when a transaction already exists for the given business key.
     * Used for pre-flush duplicate detection to provide stable error codes
     * without relying solely on database constraint violations.
     */
    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool;
}
