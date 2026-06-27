<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class DoctrineBackedVendorTransactionRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findByVendorId(string $vendorId): array
    {
        /** @var list<VendorTransactionEntity> $transactions */
        $transactions = $this->baseQueryBuilder()
            ->andWhere('transaction.vendorId = :vendorId')
            ->setParameter('vendorId', $vendorId)
            ->getQuery()
            ->getResult();

        return $transactions;
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransactionEntity
    {
        $transaction = $this->baseQueryBuilder()
            ->andWhere('transaction.id = :id')
            ->andWhere('transaction.vendorId = :vendorId')
            ->setParameter('id', $id)
            ->setParameter('vendorId', $vendorId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $transaction instanceof VendorTransactionEntity ? $transaction : null;
    }

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('COUNT(transaction.id)')
            ->from(VendorTransactionEntity::class, 'transaction')
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

    public function find(mixed $id): ?object
    {
        if (!is_scalar($id)) {
            return null;
        }

        return $this->entityManager->find(VendorTransactionEntity::class, $id);
    }

    public function findOneBy(array $criteria): ?object
    {
        return $this->entityManager
            ->getRepository(VendorTransactionEntity::class)
            ->findOneBy($criteria);
    }

    public function findBy(array $criteria): array
    {
        /** @var list<object> $transactions */
        $transactions = $this->entityManager
            ->getRepository(VendorTransactionEntity::class)
            ->findBy($criteria);

        return $transactions;
    }

    public function save(object $entity, bool $flush = false): void
    {
        if (!$entity instanceof VendorTransactionEntity) {
            return;
        }

        $this->entityManager->persist($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('transaction')
            ->from(VendorTransactionEntity::class, 'transaction')
            ->orderBy('transaction.createdAt', 'DESC')
            ->addOrderBy('transaction.id', 'DESC');
    }
}
