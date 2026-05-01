<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorMediaEntity>
 */
final class VendorMediaRepository extends ServiceEntityRepository implements VendorMediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMediaEntity::class);
    }

    public function save(VendorMediaEntity $vendorMedia, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorMedia);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorMediaEntity $vendorMedia, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorMedia);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
