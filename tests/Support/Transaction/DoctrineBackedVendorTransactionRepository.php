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

    public function find(mixed $id): ?VendorTransactionEntity
    {
        if (!is_scalar($id)) {
            return null;
        }

        $transaction = $this->entityManager->find(VendorTransactionEntity::class, $id);

        return $transaction instanceof VendorTransactionEntity ? $transaction : null;
    }

    public function byId(mixed $id): ?VendorTransactionEntity
    {
        return $this->find($id);
    }

    public function findOneBy(array $criteria): ?VendorTransactionEntity
    {
        $transaction = $this->entityManager
            ->getRepository(VendorTransactionEntity::class)
            ->findOneBy($criteria);

        return $transaction instanceof VendorTransactionEntity ? $transaction : null;
    }

    /** @return list<VendorTransactionEntity> */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $normalizedOrderBy = null;
        if (null !== $orderBy) {
            $normalizedOrderBy = [];
            foreach ($orderBy as $field => $direction) {
                if (in_array($direction, ['ASC', 'asc', 'DESC', 'desc'], true)) {
                    $normalizedOrderBy[$field] = $direction;
                }
            }
        }

        $transactions = $this->entityManager
            ->getRepository(VendorTransactionEntity::class)
            ->findBy($criteria, $normalizedOrderBy, $limit, $offset);

        return $transactions;
    }

    public function save(VendorTransactionEntity $entity, bool $flush = false): void
    {
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
