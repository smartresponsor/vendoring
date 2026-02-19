<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;


use App\RepositoryInterface\Vendor\Repository\VendorSecurityRepositoryInterface;
use App\Entity\Vendor\VendorSecurity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorSecurityRepository extends ServiceEntityRepository
    implements VendorSecurityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorSecurity::class);
    }
}
