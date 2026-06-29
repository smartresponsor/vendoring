<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorTransactionRepository extends ServiceEntityRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorTransactionEntity::class);
    }

    public function save(object $entity, bool $flush = false): void
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

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
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
