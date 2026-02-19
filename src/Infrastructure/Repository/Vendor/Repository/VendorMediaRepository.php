<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;


use App\RepositoryInterface\Vendor\Repository\VendorMediaRepositoryInterface;
use App\Entity\Vendor\VendorMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorMediaRepository extends ServiceEntityRepository
    implements VendorMediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMedia::class);
    }
}
