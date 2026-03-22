<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Vendor\VendorUserAssignment;
use App\EntityInterface\VendorUserAssignmentInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\ServiceInterface\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final class VendorUserAssignmentService implements VendorUserAssignmentServiceInterface
{
    public function __construct(
        private readonly VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentInterface
    {
        return $this->assignRole($vendorId, $userId, 'owner', true);
    }

    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentInterface
    {
        $existing = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if ($existing instanceof VendorUserAssignmentInterface) {
            if (method_exists($existing, 'activate')) {
                $existing->activate();
            }
            if (method_exists($existing, 'changeRole')) {
                $existing->changeRole($role);
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

        $assignment = new VendorUserAssignment(
            vendorId: $vendorId,
            userId: $userId,
            role: $role,
            status: 'active',
            isPrimary: $isPrimary,
        );

        $this->assignmentRepository->save($assignment, true);

        return $assignment;
    }

    public function revoke(int $vendorId, int $userId): void
    {
        $assignment = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if (!$assignment instanceof VendorUserAssignmentInterface) {
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

        if (!$assignment instanceof VendorUserAssignmentInterface) {
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
                $this->assignmentRepository->save($assignment, false);
            }
        }

        $this->entityManager->flush();
    }
}
