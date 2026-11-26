<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\RepositoryInterface\VendorTransactionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorTransactionEntity> */
final class VendorTransactionRepository extends ServiceEntityRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorTransactionEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorTransactionEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorTransactionEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorTransactionEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorTransactionEntity ? $entity : null;
    }

    public function save(VendorTransactionEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return list<VendorTransactionEntity> */
    public function findByVendorId(string $vendorId): array
    {
        return $this->findNewestByVendorId($vendorId);
    }

    /** @return list<VendorTransactionEntity> */
    public function findNewestByVendorId(string $vendorId): array
    {
        return $this->findBy(['vendorId' => $vendorId], ['createdAt' => 'DESC', 'id' => 'DESC']);
    }

    public function byId(mixed $id): ?VendorTransactionEntity
    {
        return $this->find($id);
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransactionEntity
    {
        return $this->findOneBy([
            'id' => $id,
            'vendorId' => $vendorId,
        ]);
    }

    /**
     * Returns true when a transaction already exists for the given business key.
     *
     * Null projectId uses an explicit IS NULL branch to match the split partial
     * unique index defined in the migration.
     */
    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        $qb = $this->createQueryBuilder('t')
            ->select('1')
            ->where('t.vendorId = :vendorId')
            ->andWhere('t.orderId = :orderId')
            ->setParameter('vendorId', $vendorId)
            ->setParameter('orderId', $orderId)
            ->setMaxResults(1);

        if (null === $projectId) {
            $qb->andWhere('t.projectId IS NULL');
        } else {
            $qb->andWhere('t.projectId = :projectId')
                ->setParameter('projectId', $projectId);
        }

        return null !== $qb->getQuery()->getOneOrNullResult();
    }
}
