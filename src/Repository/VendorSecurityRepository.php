<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vendor\VendorSecurity;
use App\EntityInterface\VendorSecurityInterface;
use App\RepositoryInterface\VendorSecurityRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for transitional vendor-local security state records.
 */
final class VendorSecurityRepository extends ServiceEntityRepository implements VendorSecurityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorSecurity::class);
    }

    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface
    {
        /** @var ?VendorSecurityInterface $entity */
        $entity = $this->findOneBy([
            'vendor' => $vendorId,
            'status' => 'active',
        ]);

        return $entity;
    }
}
