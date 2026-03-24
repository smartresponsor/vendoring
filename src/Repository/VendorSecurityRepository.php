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

    public function find(int $id): ?VendorSecurityInterface
    {
        /** @var ?VendorSecurityInterface $entity */
        $entity = parent::find($id);

        return $entity;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorSecurityInterface
    {
        /** @var ?VendorSecurityInterface $entity */
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        /** @var list<VendorSecurityInterface> $entities */
        $entities = parent::findBy($criteria, $orderBy, $limit, $offset);

        return $entities;
    }

    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface
    {
        return $this->findOneBy([
            'vendor' => $vendorId,
            'status' => 'active',
        ]);
    }
}
