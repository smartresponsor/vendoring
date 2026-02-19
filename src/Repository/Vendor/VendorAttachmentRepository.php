<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorAttachment;
use App\RepositoryInterface\Vendor\VendorAttachmentRepositoryInterface;
use App\RepositoryInterface\Vendor\VendorAttachmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorAttachmentRepository extends ServiceEntityRepository implements VendorAttachmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorAttachment::class);
    }
}
