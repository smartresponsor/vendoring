<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;


use App\RepositoryInterface\Vendor\Repository\VendorLedgerBindingRepositoryInterface;
use App\Entity\Vendor\VendorLedgerBinding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorLedgerBindingRepository extends ServiceEntityRepository
    implements VendorLedgerBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorLedgerBinding::class);
    }
}
