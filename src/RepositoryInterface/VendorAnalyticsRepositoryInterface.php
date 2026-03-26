<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorAnalytics;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAnalytics>
 */
interface VendorAnalyticsRepositoryInterface extends ObjectRepository
{
    public function save(VendorAnalytics $vendorAnalytics, bool $flush = false): void;

    public function remove(VendorAnalytics $vendorAnalytics, bool $flush = false): void;

    public function findOneByVendorId(string $vendorId): ?VendorAnalytics;
}
