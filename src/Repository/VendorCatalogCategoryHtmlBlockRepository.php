<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryHtmlBlockRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorCatalogCategoryHtmlBlockEntity> */
final class VendorCatalogCategoryHtmlBlockRepository extends ServiceEntityRepository implements VendorCatalogCategoryHtmlBlockRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorCatalogCategoryHtmlBlockEntity::class);
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
