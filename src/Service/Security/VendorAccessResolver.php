<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Security;

use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAccessResolverInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\Vendoring\ValueObject\VendorRole;

/**
 * Repository-backed resolver for vendor-local human/operator access decisions.
 *
 * Access is granted when at least one active assignment for the user/vendor pair maps to the
 * requested capability through the canonical authorization matrix.
 */
final readonly class VendorAccessResolver implements VendorAccessResolverInterface
{
    public function __construct(
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixInterface      $authorizationMatrix,
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
