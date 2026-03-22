<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vendor\VendorUserAssignment;
use App\EntityInterface\VendorUserAssignmentInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        return $this->findOneBy([
            'vendorId' => $vendorId,
            'status' => 'active',
            'isPrimary' => true,
        ]);
    }

    public function findActiveByVendorId(int $vendorId): array
    {
        return $this->findBy([
            'vendorId' => $vendorId,
            'status' => 'active',
        ]);
    }

    public function findActiveByUserId(int $userId): array
    {
        return $this->findBy([
            'userId' => $userId,
            'status' => 'active',
        ]);
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentInterface
    {
        return $this->findOneBy([
            'vendorId' => $vendorId,
            'userId' => $userId,
        ]);
    }
}
