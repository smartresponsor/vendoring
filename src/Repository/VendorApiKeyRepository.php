<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository;

use App\Entity\Vendor\VendorApiKey;
use App\RepositoryInterface\VendorApiKeyRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorApiKeyRepository extends ServiceEntityRepository implements VendorApiKeyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorApiKey::class);
    }

    public function save(VendorApiKey $apiKey, bool $flush = false): void
    {
        $this->getEntityManager()->persist($apiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorApiKey $vendorApiKey, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorApiKey);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByApiKey(string $apiKey): ?VendorApiKey
    {
        $candidate = $this->findOneBy([
            'apiKey' => $apiKey,
        ]);

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }

    public function findOneByVendorId(string $vendorId): ?VendorApiKey
    {
        $candidate = $this->findOneBy([
            'vendorId' => $vendorId,
        ]);

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }

    public function findActiveByToken(string $tokenHash): ?VendorApiKey
    {
        $candidate = $this->findOneBy([
            'tokenHash' => $tokenHash,
            'status' => 'active',
        ]);

        return $candidate instanceof VendorApiKey ? $candidate : null;
    }
}
