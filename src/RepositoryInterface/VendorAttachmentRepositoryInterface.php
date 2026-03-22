<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorAttachment;

interface VendorAttachmentRepositoryInterface
{
    public function save(VendorAttachment $vendorAttachment, bool $flush = false): void;

    public function remove(VendorAttachment $vendorAttachment, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorAttachment;
}
