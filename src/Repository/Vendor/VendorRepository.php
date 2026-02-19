<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\Vendor;
use App\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorRepository extends ServiceEntityRepository implements VendorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vendor::class);
    }
}
