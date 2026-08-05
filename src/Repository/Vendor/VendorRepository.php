<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorEntity> */
final class VendorRepository extends ServiceEntityRepository implements VendorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorEntity ? $entity : null;
    }

    public function save(VendorEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findIndexRows(): array
    {
        /** @var list<array{id: int, brandName: string, ownerUserId: int|null}> $rows */
        $rows = $this->createQueryBuilder('vendor')
            ->select('vendor.id', 'vendor.brandName', 'vendor.ownerUserId')
            ->orderBy('vendor.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findOneForUserId(int $userId): ?VendorEntity
    {
        $vendor = $this->createQueryBuilder('vendor')
            ->leftJoin('vendor.userAssignments', 'assignment')
            ->andWhere('vendor.ownerUserId = :userId OR (assignment.userId = :userId AND assignment.status = :activeStatus)')
            ->setParameter('userId', $userId)
            ->setParameter('activeStatus', 'active')
            ->addOrderBy('assignment.primaryAssignment', 'DESC')
            ->addOrderBy('vendor.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $vendor instanceof VendorEntity ? $vendor : null;
    }

    public function byId(mixed $id): ?VendorEntity
    {
        return $this->find($id);
    }
}
