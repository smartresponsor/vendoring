<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorBillingEntity>
 */
final class VendorBillingRepository extends ServiceEntityRepository implements VendorBillingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorBillingEntity::class);
    }

    public function save(VendorBillingEntity $vendorBilling, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorBilling);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorBillingEntity $vendorBilling, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorBilling);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorBillingEntity
    {
        $entity = $this->createQueryBuilder('billing')
            ->innerJoin('billing.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->setParameter('vendorId', (int) $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity instanceof VendorBillingEntity ? $entity : null;
    }
}
