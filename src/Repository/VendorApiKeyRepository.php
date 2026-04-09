<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository;

use App\Entity\VendorApiKey;
use App\RepositoryInterface\VendorApiKeyRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorApiKey>
 */
final class VendorApiKeyRepository extends ServiceEntityRepository implements VendorApiKeyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorApiKey::class);
    }

    /**
     * Persists the requested record.
     */
    public function save(VendorApiKey $apiKey, bool $flush = false): void
    {
        $this->getEntityManager()->persist($apiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorApiKey $vendorApiKey, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorApiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns the requested persisted state.
     */
    public function findOneByApiKey(string $apiKey): ?VendorApiKey
    {
        $candidate = $this->findOneBy([
            'tokenHash' => hash('sha256', $apiKey),
        ]);

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorId(string $vendorId): ?VendorApiKey
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

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }

    /**
     * Returns the requested persisted state.
     */
    public function findActiveByToken(string $tokenHash): ?VendorApiKey
    {
        $candidate = $this->findOneBy([
            'tokenHash' => $tokenHash,
            'status' => 'active',
        ]);

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }
}
