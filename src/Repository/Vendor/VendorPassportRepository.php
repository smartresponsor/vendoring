<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPassportEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPassportRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorPassportEntity>
 */
final class VendorPassportRepository extends ServiceEntityRepository implements VendorPassportRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorPassportEntity::class);
    }
}
