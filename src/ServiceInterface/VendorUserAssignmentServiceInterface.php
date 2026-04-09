<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EntityInterface\VendorUserAssignmentInterface;

/**
 * Write-side service for vendor-local human role assignments.
 *
 * Roles accepted by this service must belong to the canonical RBAC set defined by VendorRole.
 */
interface VendorUserAssignmentServiceInterface
{
    /**
     * Assigns the requested runtime subject.
     */
    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentInterface;

    /**
     * Assign or update one canonical role for one vendor/user pair.
     *
     * @param int    $vendorId  Canonical numeric vendor identifier.
     * @param int    $userId    Canonical numeric user identifier.
     * @param string $role      Canonical RBAC role such as `owner` or `finance`.
     * @param bool   $isPrimary When true, the assignment becomes the primary active assignment for the vendor.
     */
    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentInterface;

    /**
     * Executes the revoke operation for this runtime surface.
     */
    public function revoke(int $vendorId, int $userId): void;

    /**
     * Executes the set primary operation for this runtime surface.
     */
    public function setPrimary(int $vendorId, int $userId): void;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function listActiveForVendor(int $vendorId): array;
}
