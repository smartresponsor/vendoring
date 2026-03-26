<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorAttachment;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAttachment>
 */
interface VendorAttachmentRepositoryInterface extends ObjectRepository
{
    public function save(VendorAttachment $vendorAttachment, bool $flush = false): void;

    public function remove(VendorAttachment $vendorAttachment, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorAttachment;
}
