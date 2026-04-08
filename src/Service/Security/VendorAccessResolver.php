<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\ServiceInterface\Security\VendorAccessResolverInterface;
use App\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\ValueObject\VendorRole;

/**
 * Repository-backed resolver for vendor-local human/operator access decisions.
 *
 * Access is granted when at least one active assignment for the user/vendor pair maps to the
 * requested capability through the canonical authorization matrix.
 */
final class VendorAccessResolver implements VendorAccessResolverInterface
{
    public function __construct(
        private readonly VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private readonly VendorAuthorizationMatrixInterface $authorizationMatrix,
    ) {
    }

    /**
     * Determines whether the requested operation is allowed.
     */
    public function canUserAccessVendorCapability(int $vendorId, int $userId, string $capability): bool
    {
        return $this->explainUserAccessVendorCapability($vendorId, $userId, $capability)['granted'];
    }

    /**
     * Executes the explain user access vendor capability operation for this runtime surface.
     */
    public function explainUserAccessVendorCapability(int $vendorId, int $userId, string $capability): array
    {
        $roles = [];

        foreach ($this->assignmentRepository->findActiveByVendorId($vendorId) as $assignment) {
            if ($assignment->getUserId() !== $userId) {
                continue;
            }

            $normalizedRole = VendorRole::normalize($assignment->getRole());
            $roles[] = $normalizedRole;

            if ($this->authorizationMatrix->can($normalizedRole, $capability)) {
                return [
                    'vendorId' => $vendorId,
                    'userId' => $userId,
                    'capability' => $capability,
                    'granted' => true,
                    'roles' => array_values(array_unique($roles)),
                    'reason' => 'role_grants_capability',
                ];
            }
        }

        return [
            'vendorId' => $vendorId,
            'userId' => $userId,
            'capability' => $capability,
            'granted' => false,
            'roles' => array_values(array_unique($roles)),
            'reason' => [] === $roles ? 'no_active_assignment' : 'capability_not_granted',
        ];
    }
}
