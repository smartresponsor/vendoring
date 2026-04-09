<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\EntityInterface\VendorUserAssignmentInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<\App\Entity\VendorUserAssignment>
 */
interface VendorUserAssignmentRepositoryInterface extends ObjectRepository
{
    public function save(VendorUserAssignmentInterface $assignment, bool $flush = false): void;

    public function remove(VendorUserAssignmentInterface $assignment, bool $flush = false): void;

    public function findPrimaryForVendorId(int $vendorId): ?VendorUserAssignmentInterface;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function findActiveByVendorId(int $vendorId): array;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function findActiveByUserId(int $userId): array;

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentInterface;
}
