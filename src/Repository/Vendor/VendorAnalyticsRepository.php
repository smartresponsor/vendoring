<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorAnalyticsEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorAnalyticsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorAnalyticsEntity>
 */
final class VendorAnalyticsRepository extends ServiceEntityRepository implements VendorAnalyticsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAnalyticsEntity::class);
    }

    public function save(VendorAnalyticsEntity $vendorAnalytics, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorAnalytics);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorAnalyticsEntity $vendorAnalytics, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorAnalytics);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorAnalyticsEntity
    {
        $entity = $this->createQueryBuilder('analytics')
            ->innerJoin('analytics.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->setParameter('vendorId', (int) $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity instanceof VendorAnalyticsEntity ? $entity : null;
    }
}
