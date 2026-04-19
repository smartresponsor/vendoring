<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorSecurity;
use App\Vendoring\EntityInterface\VendorSecurityInterface;
use App\Vendoring\RepositoryInterface\VendorSecurityRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for transitional vendor-local security state records.
 */
/**
 * @extends ServiceEntityRepository<VendorSecurity>
 */
final class VendorSecurityRepository extends ServiceEntityRepository implements VendorSecurityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorSecurity::class);
    }

    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface
    {
        $entity = $this->findOneBy([
            'vendor' => $vendorId,
            'status' => 'active',
        ]);

        return $entity instanceof VendorSecurityInterface ? $entity : null;
    }
}
