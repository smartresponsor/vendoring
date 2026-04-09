<?php

declare(strict_types=1);

namespace App\Service;

use App\Projection\VendorOwnershipView;
use App\RepositoryInterface\VendorRepositoryInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final readonly class VendorOwnershipViewBuilder implements VendorOwnershipViewBuilderInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixInterface $authorizationMatrix,
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
                'capabilities' => $this->authorizationMatrix->capabilitiesForRole($assignment->getRole()),
                'isPrimary' => $assignment->isPrimary(),
                'grantedAt' => $assignment->getGrantedAt()->format(DATE_ATOM),
                'revokedAt' => $assignment->getRevokedAt()?->format(DATE_ATOM),
            ];
        }

        return new VendorOwnershipView(
            vendorId: $vendorId,
            ownerUserId: method_exists($vendor, 'getOwnerUserId') ? $vendor->getOwnerUserId() : null,
            assignments: $assignments,
        );
    }
}
