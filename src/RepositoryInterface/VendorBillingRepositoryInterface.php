<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\VendorBilling;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorBilling>
 */
interface VendorBillingRepositoryInterface extends ObjectRepository
{
    /**
     * Persists the requested record.
     */
    public function save(VendorBilling $vendorBilling, bool $flush = false): void;

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorBilling $vendorBilling, bool $flush = false): void;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorId(string $vendorId): ?VendorBilling;
}
