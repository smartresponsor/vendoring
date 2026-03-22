<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EntityInterface\VendorUserAssignmentInterface;

interface VendorUserAssignmentServiceInterface
{
    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentInterface;

    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentInterface;

    public function revoke(int $vendorId, int $userId): void;

    public function setPrimary(int $vendorId, int $userId): void;

    /**
     * @return list<VendorUserAssignmentInterface>
     */
    public function listActiveForVendor(int $vendorId): array;
}
