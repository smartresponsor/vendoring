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
    public function findNewestByVendorId(string $vendorId): array
    {
        return $this->findBy(['vendorId' => $vendorId], ['createdAt' => 'DESC', 'id' => 'DESC']);
    }

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
