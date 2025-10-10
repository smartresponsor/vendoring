<?php
declare(strict_types=1);

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorSecurity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorSecurityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorSecurity::class);
    }
}
