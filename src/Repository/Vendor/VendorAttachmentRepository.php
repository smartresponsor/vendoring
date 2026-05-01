<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorAttachmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorAttachmentEntity>
 */
final class VendorAttachmentRepository extends ServiceEntityRepository implements VendorAttachmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAttachmentEntity::class);
    }

    public function save(VendorAttachmentEntity $vendorAttachment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorAttachment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorAttachmentEntity $vendorAttachment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorAttachment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorAttachmentEntity
    {
        $entity = $this->findOneBy(['vendorId' => $vendorId]);

        return $entity instanceof VendorAttachmentEntity ? $entity : null;
    }
}
