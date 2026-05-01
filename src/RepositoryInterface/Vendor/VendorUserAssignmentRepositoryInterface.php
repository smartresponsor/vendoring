<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorUserAssignmentEntity>
 */
interface VendorUserAssignmentRepositoryInterface extends ObjectRepository
{
    public function save(VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void;

    public function remove(VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void;

    public function findPrimaryForVendorId(int $vendorId): ?VendorUserAssignmentEntityInterface;

    /**
     * @return list<VendorUserAssignmentEntityInterface>
     */
    public function findActiveByVendorId(int $vendorId): array;

    /**
     * @return list<VendorUserAssignmentEntityInterface>
     */
    public function findActiveByUserId(int $userId): array;

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentEntityInterface;
}
