<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Ownership;

use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\RepositoryInterface\VendorOwnershipProjectionRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixServiceInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final readonly class VendorOwnershipProjectionBuilder implements VendorOwnershipProjectionBuilderInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixServiceInterface $authorizationMatrix,
        private VendorOwnershipProjectionRepositoryInterface $ownershipProjectionRepository,
    ) {
    }

    public function buildForVendorId(int $vendorId): ?VendorOwnershipProjection
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (!$vendor instanceof VendorEntity) {
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

        return new VendorOwnershipProjection(
            vendorId: $vendorId,
            ownerUserId: $vendor->getOwnerUserId(),
            assignments: $assignments,
            relationCounts: $this->ownershipProjectionRepository->relationCounts($vendor),
        );
    }
}
