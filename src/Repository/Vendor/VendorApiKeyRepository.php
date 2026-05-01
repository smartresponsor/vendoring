<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorApiKeyEntity>
 */
final class VendorApiKeyRepository extends ServiceEntityRepository implements VendorApiKeyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorApiKeyEntity::class);
    }

    public function save(VendorApiKeyEntity $vendorApiKey, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorApiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorApiKeyEntity $vendorApiKey, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorApiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByApiKey(string $apiKey): ?VendorApiKeyEntity
    {
        $candidate = $this->findOneBy([
            'tokenHash' => hash('sha256', $apiKey),
        ]);

        return $candidate instanceof VendorApiKeyEntity ? $candidate : null;
    }

    public function findOneByVendorId(string $vendorId): ?VendorApiKeyEntity
    {
        if (!ctype_digit($vendorId)) {
            return null;
        }

        $candidate = $this->createQueryBuilder('apiKey')
            ->andWhere('IDENTITY(apiKey.vendor) = :vendorId')
            ->setParameter('vendorId', (int) $vendorId)
            ->orderBy('apiKey.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $candidate instanceof VendorApiKeyEntity ? $candidate : null;
    }

    public function findActiveByToken(string $tokenHash): ?VendorApiKeyEntity
    {
        $candidate = $this->findOneBy([
            'tokenHash' => $tokenHash,
            'status' => 'active',
        ]);

        return $candidate instanceof VendorApiKeyEntity ? $candidate : null;
    }
}
