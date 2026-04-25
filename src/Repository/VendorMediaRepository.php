<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorMedia;
use App\Vendoring\RepositoryInterface\VendorMediaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorMedia>
 */
final class VendorMediaRepository extends ServiceEntityRepository implements VendorMediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMedia::class);
    }

    public function save(VendorMedia $vendorMedia, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorMedia);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorMedia $vendorMedia, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorMedia);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
