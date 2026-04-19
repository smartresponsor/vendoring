<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Entity\VendorUserAssignment;
use App\Vendoring\EntityInterface\VendorUserAssignmentInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\VendorUserAssignmentServiceInterface;
use App\Vendoring\ValueObject\VendorRole;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class VendorUserAssignmentService implements VendorUserAssignmentServiceInterface
{
    public function __construct(
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private EntityManagerInterface                  $entityManager,
    ) {}

    public function assignOwner(int $vendorId, int $userId): VendorUserAssignmentInterface
    {
        return $this->assignRole($vendorId, $userId, VendorRole::OWNER, true);
    }

    public function assignRole(int $vendorId, int $userId, string $role, bool $isPrimary = false): VendorUserAssignmentInterface
    {
        $normalizedRole = VendorRole::normalize($role);

        if (!VendorRole::isValid($normalizedRole)) {
            throw new InvalidArgumentException(sprintf('Unsupported vendor role "%s".', $role));
        }

        $existing = $this->assignmentRepository->findOneByVendorIdAndUserId($vendorId, $userId);

        if ($existing instanceof VendorUserAssignmentInterface) {
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

        $assignment = new VendorUserAssignment(
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
                $this->assignmentRepository->save($assignment);
            }
        }

        $this->entityManager->flush();
    }
}
