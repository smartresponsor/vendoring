<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorAnalytics;
use App\RepositoryInterface\Vendor\VendorAnalyticsRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorAnalyticsRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorAnalyticsRepository extends ServiceEntityRepository implements VendorAnalyticsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAnalytics::class);
    }
}
