<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\RepositoryInterface\VendorLedgerBindingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorLedgerBindingEntity> */
final class VendorLedgerBindingRepository extends ServiceEntityRepository implements VendorLedgerBindingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorLedgerBindingEntity::class);
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
