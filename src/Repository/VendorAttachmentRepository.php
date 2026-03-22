<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vendor\VendorAttachment;
use App\RepositoryInterface\VendorAttachmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendorAttachment>
 */
final class VendorAttachmentRepository extends ServiceEntityRepository implements VendorAttachmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAttachment::class);
    }

    public function save(VendorAttachment $vendorAttachment, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vendorAttachment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorAttachment $vendorAttachment, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vendorAttachment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByVendorId(string $vendorId): ?VendorAttachment
    {
        $entity = $this->findOneBy(['vendorId' => $vendorId]);

        return $entity instanceof VendorAttachment ? $entity : null;
    }
}
