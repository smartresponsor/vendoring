<?php
declare(strict_types=1);

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAttachment::class);
    }
}
