<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryPinRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorCatalogCategoryPinEntity> */
final class VendorCatalogCategoryPinRepository extends ServiceEntityRepository implements VendorCatalogCategoryPinRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorCatalogCategoryPinEntity::class);
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findOneByCategoryAndRecord(string $categoryId, string $recordId): ?object
    {
        return $this->findOneBy([
            'objectCode.objectCode' => $categoryId,
            'recordId' => $recordId,
        ]);
    }

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
