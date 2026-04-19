<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorBilling;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorBilling>
 */
final class VendorBillingRepository extends ServiceEntityRepository implements VendorBillingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorBilling::class);
    }

    public function save(VendorBilling $vendorBilling, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorBilling);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorBilling $vendorBilling, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorBilling);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorBilling
    {
        $entity = $this->createQueryBuilder('billing')
            ->innerJoin('billing.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->setParameter('vendorId', (int) $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity instanceof VendorBilling ? $entity : null;
    }
}
