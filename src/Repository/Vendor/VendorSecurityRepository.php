<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorSecurityEntity;
use App\Vendoring\EntityInterface\Vendor\VendorSecurityEntityInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorSecurityRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for transitional vendor-local security state records.
 *
 * @extends ServiceEntityRepository<VendorSecurityEntity>
 */
final class VendorSecurityRepository extends ServiceEntityRepository implements VendorSecurityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorSecurityEntity::class);
    }

    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityEntityInterface
    {
        $entity = $this->findOneBy([
            'vendor' => $vendorId,
            'status' => 'active',
        ]);

        return $entity instanceof VendorSecurityEntityInterface ? $entity : null;
    }

    public function save(VendorSecurityEntity $vendorSecurity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorSecurity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
