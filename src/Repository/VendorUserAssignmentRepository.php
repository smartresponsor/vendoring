<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorUserAssignment;
use App\Vendoring\EntityInterface\VendorUserAssignmentInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
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
        return $this->typedOne(
            $this->findOneBy([
                'vendorId' => $vendorId,
                'status' => 'active',
                'isPrimary' => true,
            ]),
        );
    }

    public function findActiveByVendorId(int $vendorId): array
    {
        return $this->typedList(
            $this->findBy([
                'vendorId' => $vendorId,
                'status' => 'active',
            ]),
        );
    }

    public function findActiveByUserId(int $userId): array
    {
        return $this->typedList(
            $this->findBy([
                'userId' => $userId,
                'status' => 'active',
            ]),
        );
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentInterface
    {
        return $this->typedOne(
            $this->findOneBy([
                'vendorId' => $vendorId,
                'userId' => $userId,
            ]),
        );
    }

    /**
     * @param list<VendorUserAssignment> $entities
     *
     * @return list<VendorUserAssignmentInterface>
     */
    private function typedList(array $entities): array
    {
        return $entities;
    }

    private function typedOne(?VendorUserAssignment $entity): ?VendorUserAssignmentInterface
    {
        return $entity;
    }
}
