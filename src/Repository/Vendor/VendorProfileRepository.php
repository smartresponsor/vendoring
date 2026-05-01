<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorProfileRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorProfileEntity>
 */
final class VendorProfileRepository extends ServiceEntityRepository implements VendorProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorProfileEntity::class);
    }

    public function save(VendorProfileEntity $vendorProfile, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorProfile);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorProfileEntity $vendorProfile, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorProfile);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
