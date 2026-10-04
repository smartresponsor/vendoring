<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorMediaAttachmentEntity;
use App\Vendoring\RepositoryInterface\VendorMediaAttachmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorMediaAttachmentEntity> */
final class VendorMediaAttachmentRepository extends ServiceEntityRepository implements VendorMediaAttachmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorMediaAttachmentEntity::class);
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

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
