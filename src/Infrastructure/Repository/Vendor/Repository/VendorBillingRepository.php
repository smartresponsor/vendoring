<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;


use App\RepositoryInterface\Vendor\Repository\VendorBillingRepositoryInterface;
use App\Entity\Vendor\VendorBilling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorBillingRepository extends ServiceEntityRepository
    implements VendorBillingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorBilling::class);
    }
}
