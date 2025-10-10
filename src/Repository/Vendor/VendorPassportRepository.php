<?php
declare(strict_types=1);

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorPassport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorPassportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorPassport::class);
    }
}
