<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

interface VendorUserAssignmentRepositoryInterface
{
    public function find(mixed $id): ?object;

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?object;

    /** @return list<object> */
    public function findActiveByVendorId(int $vendorId): array;

    public function save(object $entity, bool $flush = false): void;
}
