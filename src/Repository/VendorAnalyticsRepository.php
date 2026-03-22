<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository;

use App\Entity\Vendor\VendorAnalytics;
use App\RepositoryInterface\VendorAnalyticsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        $entity = $this->findOneBy(['vendorId' => $vendorId]);

        return $entity instanceof VendorAnalytics ? $entity : null;
    }
}
