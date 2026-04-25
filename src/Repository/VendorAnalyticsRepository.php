<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorAnalytics;
use App\Vendoring\RepositoryInterface\VendorAnalyticsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorAnalytics>
 */
final class VendorAnalyticsRepository extends ServiceEntityRepository implements VendorAnalyticsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAnalytics::class);
    }

    public function save(VendorAnalytics $vendorAnalytics, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorAnalytics);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorAnalytics $vendorAnalytics, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorAnalytics);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorAnalytics
    {
        $entity = $this->createQueryBuilder('analytics')
            ->innerJoin('analytics.vendor', 'vendor')
            ->andWhere('vendor.id = :vendorId')
            ->setParameter('vendorId', (int) $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity instanceof VendorAnalytics ? $entity : null;
    }
}
