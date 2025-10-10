<?php
declare(strict_types=1);

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorAnalytics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorAnalyticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAnalytics::class);
    }
}
