<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Transaction;

use App\Vendoring\Entity\VendorTransaction;
use App\Vendoring\RepositoryInterface\VendorTransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class DoctrineBackedVendorTransactionRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function findByVendorId(string $vendorId): array
    {
        /** @var list<VendorTransaction> $transactions */
        $transactions = $this->baseQueryBuilder()
            ->andWhere('transaction.vendorId = :vendorId')
            ->setParameter('vendorId', $vendorId)
            ->getQuery()
            ->getResult();

        return $transactions;
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction
    {
        $transaction = $this->baseQueryBuilder()
            ->andWhere('transaction.id = :id')
            ->andWhere('transaction.vendorId = :vendorId')
            ->setParameter('id', $id)
            ->setParameter('vendorId', $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $transaction instanceof VendorTransaction ? $transaction : null;
    }

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('COUNT(transaction.id)')
            ->from(VendorTransaction::class, 'transaction')
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

        return (int) $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('transaction')
            ->from(VendorTransaction::class, 'transaction')
            ->orderBy('transaction.createdAt', 'DESC')
            ->addOrderBy('transaction.id', 'DESC');
    }
}
