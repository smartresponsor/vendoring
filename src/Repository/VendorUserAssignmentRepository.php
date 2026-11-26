<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorUserAssignmentEntity> */
final class VendorUserAssignmentRepository extends ServiceEntityRepository implements VendorUserAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorUserAssignmentEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorUserAssignmentEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorUserAssignmentEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorUserAssignmentEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorUserAssignmentEntity ? $entity : null;
    }

    public function save(VendorUserAssignmentEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentEntity
    {
        $result = $this->createQueryBuilder('assignment')
            ->innerJoin('assignment.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->andWhere('assignment.userId = :userId')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof VendorUserAssignmentEntity ? $result : null;
    }

    /** @return list<VendorUserAssignmentEntity> */
    public function findActiveByVendorId(int $vendorId): array
    {
        $result = $this->createQueryBuilder('assignment')
            ->innerJoin('assignment.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->andWhere('assignment.status = :status')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('status', 'active')
            ->orderBy('assignment.primaryAssignment', 'DESC')
            ->addOrderBy('assignment.grantedAt', 'DESC')
            ->getQuery()
            ->getResult();

        if (!is_array($result)) {
            return [];
        }

        return array_values(array_filter(
            $result,
            static fn (mixed $assignment): bool => $assignment instanceof VendorUserAssignmentEntity,
        ));
    }

    public function byId(mixed $id): ?VendorUserAssignmentEntity
    {
        return $this->find($id);
    }
}
