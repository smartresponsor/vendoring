<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\RepositoryInterface\VendorMediaRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorMediaEntity> */
final class VendorMediaRepository extends ServiceEntityRepository implements VendorMediaRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMediaEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorMediaEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorMediaEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorMediaEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorMediaEntity ? $entity : null;
    }

    public function save(VendorMediaEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function byId(mixed $id): ?VendorMediaEntity
    {
        return $this->find($id);
    }
}
