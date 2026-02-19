<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorMedia;
use App\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorMediaRepository extends ServiceEntityRepository implements VendorMediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMedia::class);
    }
}
