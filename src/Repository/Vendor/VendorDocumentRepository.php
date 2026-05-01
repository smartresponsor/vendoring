<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorDocumentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorDocumentEntity>
 */
final class VendorDocumentRepository extends ServiceEntityRepository implements VendorDocumentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorDocumentEntity::class);
    }

    public function save(VendorDocumentEntity $vendorDocument, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorDocument);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorDocumentEntity $vendorDocument, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorDocument);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
