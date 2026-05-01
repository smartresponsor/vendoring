<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorBillingEntity>
 */
interface VendorBillingRepositoryInterface extends ObjectRepository
{
    public function save(VendorBillingEntity $vendorBilling, bool $flush = false): void;

    public function remove(VendorBillingEntity $vendorBilling, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorBillingEntity;
}
