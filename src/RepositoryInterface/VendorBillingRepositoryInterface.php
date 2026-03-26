<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorBilling;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorBilling>
 */
interface VendorBillingRepositoryInterface extends ObjectRepository
{
    public function save(VendorBilling $vendorBilling, bool $flush = false): void;

    public function remove(VendorBilling $vendorBilling, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorBilling;
}
