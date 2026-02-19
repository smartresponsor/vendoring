<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorTransaction;
use App\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorTransactionRepository extends ServiceEntityRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorTransaction::class);
    }

    /**
     * @return list<VendorTransaction>
     */
    public function findByVendorId(string $vendorId): array
    {
        /** @var list<VendorTransaction> $res */
        $res = $this->findBy(['vendorId' => $vendorId]);
        return $res;
    }
}
