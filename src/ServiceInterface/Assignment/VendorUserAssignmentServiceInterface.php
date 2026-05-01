<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Assignment;

use App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface;

/**
 * Write-side service for vendor-local human role assignments.
 *
 * Roles accepted by this service must belong to the canonical RBAC set defined by VendorRoleValueObject.
 */
interface VendorUserAssignmentServiceInterface
{
    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentEntityInterface;

    /**
     * Assign or update one canonical role for one vendor/user pair.
     *
     * @param int    $vendorId  Canonical numeric vendor identifier.
     * @param int    $userId    Canonical numeric user identifier.
     * @param string $role      Canonical RBAC role such as `owner` or `finance`.
     * @param bool   $isPrimary When true, the assignment becomes the primary active assignment for the vendor.
     */
    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentEntityInterface;

    public function revoke(int $vendorId, int $userId): void;

    public function setPrimary(int $vendorId, int $userId): void;

    /**
     * @return list<VendorUserAssignmentEntityInterface>
     */
    public function listActiveForVendor(int $vendorId): array;
}
