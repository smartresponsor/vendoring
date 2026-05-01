<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorAnalyticsEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAnalyticsEntity>
 */
interface VendorAnalyticsRepositoryInterface extends ObjectRepository
{
    public function save(VendorAnalyticsEntity $vendorAnalytics, bool $flush = false): void;

    public function remove(VendorAnalyticsEntity $vendorAnalytics, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorAnalyticsEntity;
}
