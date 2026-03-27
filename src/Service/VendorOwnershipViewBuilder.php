<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\Projection\VendorOwnershipView;
use App\RepositoryInterface\VendorRepositoryInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final class VendorOwnershipViewBuilder implements VendorOwnershipViewBuilderInterface
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorUserAssignmentRepositoryInterface $assignmentRepository,
    ) {
    }

    public function buildForVendorId(int $vendorId): ?VendorOwnershipView
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return null;
        }

        $assignments = [];
        foreach ($this->assignmentRepository->findActiveByVendorId($vendorId) as $assignment) {
            $assignments[] = [
                'userId' => $assignment->getUserId(),
                'role' => $assignment->getRole(),
                'status' => $assignment->getStatus(),
                'isPrimary' => $assignment->isPrimary(),
                'grantedAt' => $assignment->getGrantedAt()->format(DATE_ATOM),
                'revokedAt' => $assignment->getRevokedAt()?->format(DATE_ATOM),
            ];
        }

        return new VendorOwnershipView(
            vendorId: $vendorId,
            ownerUserId: $vendor->getOwnerUserId(),
            assignments: $assignments,
        );
    }
}
