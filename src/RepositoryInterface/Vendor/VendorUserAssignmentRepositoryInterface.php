<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;

interface VendorUserAssignmentRepositoryInterface
{
    public function find(mixed $id): ?VendorUserAssignmentEntity;

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentEntity;

    /** @return list<VendorUserAssignmentEntity> */
    public function findActiveByVendorId(int $vendorId): array;

    public function save(VendorUserAssignmentEntity $entity, bool $flush = false): void;
}
