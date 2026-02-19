<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorPassport;
use App\RepositoryInterface\Vendor\VendorPassportRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorPassportRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorPassportRepository extends ServiceEntityRepository implements VendorPassportRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorPassport::class);
    }
}
