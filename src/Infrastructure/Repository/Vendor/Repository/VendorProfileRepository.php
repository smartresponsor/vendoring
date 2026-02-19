<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;


use App\RepositoryInterface\Vendor\Repository\VendorProfileRepositoryInterface;
use App\Entity\Vendor\VendorProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorProfileRepository extends ServiceEntityRepository
    implements VendorProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorProfile::class);
    }
}
