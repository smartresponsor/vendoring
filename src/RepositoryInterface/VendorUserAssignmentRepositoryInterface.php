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
    /**
     * Persists the requested record.
     */
    public function save(VendorUserAssignmentInterface $assignment, bool $flush = false): void;

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorUserAssignmentInterface $assignment, bool $flush = false): void;

    /**
     * Returns the requested persisted state.
     */
    public function findPrimaryForVendorId(int $vendorId): ?VendorUserAssignmentInterface;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function findActiveByVendorId(int $vendorId): array;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function findActiveByUserId(int $userId): array;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentInterface;
}
