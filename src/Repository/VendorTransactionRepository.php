<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorTransaction;
use App\Vendoring\RepositoryInterface\VendorTransactionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorTransaction>
 */
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
        $res = $this->findBy(['vendorId' => $vendorId], ['createdAt' => 'DESC', 'id' => 'DESC']);

        return $res;
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction
    {
        $transaction = $this->findOneBy(['id' => $id, 'vendorId' => $vendorId]);

        return $transaction instanceof VendorTransaction ? $transaction : null;
    }

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        $queryBuilder = $this->createQueryBuilder('transaction')
            ->select('1')
            ->andWhere('transaction.vendorId = :vendorId')
            ->andWhere('transaction.orderId = :orderId')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('orderId', $orderId)
            ->setMaxResults(1);

        if (null === $projectId) {
            $queryBuilder->andWhere('transaction.projectId IS NULL');
        } else {
            $queryBuilder
                ->andWhere('transaction.projectId = :projectId')
                ->setParameter('projectId', $projectId);
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return null !== $result;
    }
}
