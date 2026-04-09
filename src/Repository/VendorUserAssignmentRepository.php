<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\VendorUserAssignment;
use App\EntityInterface\VendorUserAssignmentInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorUserAssignment>
 */
final class VendorUserAssignmentRepository extends ServiceEntityRepository implements VendorUserAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorUserAssignment::class);
    }

    public function save(VendorUserAssignmentInterface $assignment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($assignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorUserAssignmentInterface $assignment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($assignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPrimaryForVendorId(int $vendorId): ?VendorUserAssignmentInterface
    {
        $entity = $this->findOneBy([
            'vendorId' => $vendorId,
            'status' => 'active',
            'isPrimary' => true,
        ]);

        return $entity instanceof VendorUserAssignmentInterface ? $entity : null;
    }

    public function findActiveByVendorId(int $vendorId): array
    {
        $entities = $this->findBy([
            'vendorId' => $vendorId,
            'status' => 'active',
        ]);

        return array_values(array_filter($entities, static fn (mixed $entity): bool => $entity instanceof VendorUserAssignmentInterface));
    }

    public function findActiveByUserId(int $userId): array
    {
        $entities = $this->findBy([
            'userId' => $userId,
            'status' => 'active',
        ]);

        return array_values(array_filter($entities, static fn (mixed $entity): bool => $entity instanceof VendorUserAssignmentInterface));
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentInterface
    {
        $entity = $this->findOneBy([
            'vendorId' => $vendorId,
            'userId' => $userId,
        ]);

        return $entity instanceof VendorUserAssignmentInterface ? $entity : null;
    }
}
