<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorLedgerBinding;
use App\RepositoryInterface\Vendor\VendorLedgerBindingRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorLedgerBindingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorLedgerBindingRepository extends ServiceEntityRepository implements VendorLedgerBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorLedgerBinding::class);
    }
}
