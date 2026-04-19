<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Projection\VendorOwnershipView;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\Vendoring\ServiceInterface\VendorOwnershipViewBuilderInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final readonly class VendorOwnershipViewBuilder implements VendorOwnershipViewBuilderInterface
{
    public function __construct(
        private VendorRepositoryInterface               $vendorRepository,
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixInterface      $authorizationMatrix,
    ) {}

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
            ownerUserId: $vendor->getOwnerUserId(),
            assignments: $assignments,
        );
    }
}
