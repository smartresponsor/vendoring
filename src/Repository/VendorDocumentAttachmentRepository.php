<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorDocumentAttachmentEntity;
use App\Vendoring\RepositoryInterface\VendorDocumentAttachmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorDocumentAttachmentEntity> */
final class VendorDocumentAttachmentRepository extends ServiceEntityRepository implements VendorDocumentAttachmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorDocumentAttachmentEntity::class);
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
