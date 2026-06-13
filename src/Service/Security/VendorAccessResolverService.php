<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Security;

use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAccessResolverServiceInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixServiceInterface;
use App\Vendoring\ValueObject\VendorRoleValueObject;

/**
 * Repository-backed resolver for vendor-local human/operator access decisions.
 *
 * Access is granted when at least one active assignment for the user/vendor pair maps to the
 * requested capability through the canonical authorization matrix.
 */
final readonly class VendorAccessResolverService implements VendorAccessResolverServiceInterface
{
    public function __construct(
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixServiceInterface      $authorizationMatrix,
    ) {}

    public function canUserAccessVendorCapability(int $vendorId, int $userId, string $capability): bool
    {
        return $this->explainUserAccessVendorCapability($vendorId, $userId, $capability)['granted'];
    }

    public function explainUserAccessVendorCapability(int $vendorId, int $userId, string $capability): array
    {
        $roles = [];

        foreach ($this->assignmentRepository->findActiveByVendorId($vendorId) as $assignment) {
            if ($assignment->getUserId() !== $userId) {
                continue;
            }

            $normalizedRole = VendorRoleValueObject::normalize($assignment->getRole());
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
