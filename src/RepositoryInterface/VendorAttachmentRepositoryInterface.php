<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\VendorAttachment;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAttachment>
 */
interface VendorAttachmentRepositoryInterface extends ObjectRepository
{
    /**
     * Persists the requested record.
     */
    public function save(VendorAttachment $vendorAttachment, bool $flush = false): void;

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorAttachment $vendorAttachment, bool $flush = false): void;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorId(string $vendorId): ?VendorAttachment;
}
