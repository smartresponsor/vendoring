<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAttachmentEntity>
 */
interface VendorAttachmentRepositoryInterface extends ObjectRepository
{
    public function save(VendorAttachmentEntity $vendorAttachment, bool $flush = false): void;

    public function remove(VendorAttachmentEntity $vendorAttachment, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorAttachmentEntity;
}
