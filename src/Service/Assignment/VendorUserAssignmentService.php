<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Assignment;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use App\Vendoring\ValueObject\VendorRoleValueObject;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class VendorUserAssignmentService implements VendorUserAssignmentServiceInterface
{
    public function __construct(
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private EntityManagerInterface                  $entityManager,
    ) {}

    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentEntityInterface
    {
        return $this->assignRole($vendorId, $userId, VendorRoleValueObject::OWNER, true);
    }

    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentEntityInterface
    {
        $normalizedRole = VendorRoleValueObject::normalize($role);

        if (!VendorRoleValueObject::isValid($normalizedRole)) {
            throw new InvalidArgumentException(sprintf('Unsupported vendor role "%s".', $role));
        }

        $existing = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if ($existing instanceof VendorUserAssignmentEntityInterface) {
            if (method_exists($existing, 'activate')) {
                $existing->activate();
            }
            if (method_exists($existing, 'changeRole')) {
                $existing->changeRole($normalizedRole);
            }
            if ($isPrimary && method_exists($existing, 'markPrimary')) {
                $this->clearPrimaryForVendor($vendorId);
                $existing->markPrimary();
            }

            $this->assignmentRepository->save($existing, true);

            return $existing;
        }

        if ($isPrimary) {
            $this->clearPrimaryForVendor($vendorId);
        }

        $assignment = new VendorUserAssignmentEntity(
            vendorId: $vendorId,
            userId: $userId,
            role: $normalizedRole,
            status: 'active',
            isPrimary: $isPrimary,
        );

        $this->assignmentRepository->save($assignment, true);

        return $assignment;
    }

    public function revoke(int $vendorId, int $userId): void
    {
        $assignment = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if (!$assignment instanceof VendorUserAssignmentEntityInterface) {
            return;
        }

        if (method_exists($assignment, 'revoke')) {
            $assignment->revoke();
        }

        $this->assignmentRepository->save($assignment, true);
    }

    public function setPrimary(int $vendorId, int $userId): void
    {
        $assignment = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if (!$assignment instanceof VendorUserAssignmentEntityInterface) {
            return;
        }

        $this->clearPrimaryForVendor($vendorId);

        if (method_exists($assignment, 'markPrimary')) {
            $assignment->markPrimary();
        }

        $this->assignmentRepository->save($assignment, true);
    }

    public function listActiveForVendor(int $vendorId): array
    {
        return $this->assignmentRepository->findActiveByVendorId($vendorId);
    }

    private function clearPrimaryForVendor(int $vendorId): void
    {
        foreach ($this->assignmentRepository->findActiveByVendorId($vendorId) as $assignment) {
            if (method_exists($assignment, 'clearPrimary')) {
                $assignment->clearPrimary();
                $this->assignmentRepository->save($assignment);
            }
        }

        $this->entityManager->flush();
    }
}
