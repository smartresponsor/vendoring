<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\VendorAnalytics;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorAnalytics>
 */
interface VendorAnalyticsRepositoryInterface extends ObjectRepository
{
    /**
     * Persists the requested record.
     */
    public function save(VendorAnalytics $vendorAnalytics, bool $flush = false): void;

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorAnalytics $vendorAnalytics, bool $flush = false): void;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorId(string $vendorId): ?VendorAnalytics;
}
