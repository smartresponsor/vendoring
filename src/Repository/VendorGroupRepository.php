<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\RepositoryInterface\VendorGroupRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorGroupEntity> */
final class VendorGroupRepository extends ServiceEntityRepository implements VendorGroupRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorGroupEntity::class);
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
