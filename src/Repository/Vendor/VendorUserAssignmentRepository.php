<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorUserAssignmentEntity>
 */
final class VendorUserAssignmentRepository extends ServiceEntityRepository implements VendorUserAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorUserAssignmentEntity::class);
    }

    public function save(VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($assignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($assignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPrimaryForVendorId(int $vendorId): ?VendorUserAssignmentEntityInterface
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

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentEntityInterface
    {
        return $this->typedOne(
            $this->findOneBy([
                'vendorId' => $vendorId,
                'userId' => $userId,
            ]),
        );
    }

    /**
     * @param list<VendorUserAssignmentEntity> $entities
     *
     * @return list<VendorUserAssignmentEntityInterface>
     */
    private function typedList(array $entities): array
    {
        return $entities;
    }

    private function typedOne(?VendorUserAssignmentEntity $entity): ?VendorUserAssignmentEntityInterface
    {
        return $entity;
    }
}
