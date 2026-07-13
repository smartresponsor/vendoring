<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorBillingEntity> */
final class VendorBillingRepository extends ServiceEntityRepository implements VendorBillingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorBillingEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorBillingEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorBillingEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorBillingEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorBillingEntity ? $entity : null;
    }

    public function save(VendorBillingEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function byId(mixed $id): ?VendorBillingEntity
    {
        return $this->find($id);
    }

    public function findOneByVendorId(int $vendorId): ?VendorBillingEntity
    {
        $result = $this->createQueryBuilder('billing')
            ->innerJoin('billing.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->setParameter('vendorId', $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof VendorBillingEntity ? $result : null;
    }
}
