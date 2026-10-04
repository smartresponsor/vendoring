<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\RepositoryInterface\VendorRememberMeTokenRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorRememberMeTokenEntity> */
final class VendorRememberMeTokenRepository extends ServiceEntityRepository implements VendorRememberMeTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorRememberMeTokenEntity::class);
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
