<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryChangeRequestEntity;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryChangeRequestRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorCatalogCategoryChangeRequestEntity> */
final class VendorCatalogCategoryChangeRequestRepository extends ServiceEntityRepository implements VendorCatalogCategoryChangeRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorCatalogCategoryChangeRequestEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorCatalogCategoryChangeRequestEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorCatalogCategoryChangeRequestEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorCatalogCategoryChangeRequestEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorCatalogCategoryChangeRequestEntity ? $entity : null;
    }

    public function save(VendorCatalogCategoryChangeRequestEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function byId(mixed $id): ?VendorCatalogCategoryChangeRequestEntity
    {
        return $this->find($id);
    }
}
