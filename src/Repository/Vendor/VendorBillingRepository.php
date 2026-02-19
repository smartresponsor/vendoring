<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorBilling;
use App\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorBillingRepository extends ServiceEntityRepository implements VendorBillingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorBilling::class);
    }
}
