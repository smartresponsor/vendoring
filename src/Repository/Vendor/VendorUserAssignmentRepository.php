<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorUserAssignmentRepository extends ServiceEntityRepository implements VendorUserAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorUserAssignmentEntity::class);
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?object
    {
        return $this->createQueryBuilder('assignment')
            ->innerJoin('assignment.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->andWhere('assignment.userId = :userId')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByVendorId(int $vendorId): array
    {
        return $this->createQueryBuilder('assignment')
            ->innerJoin('assignment.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->andWhere('assignment.status = :status')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('status', 'active')
            ->orderBy('assignment.primaryAssignment', 'DESC')
            ->addOrderBy('assignment.grantedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
