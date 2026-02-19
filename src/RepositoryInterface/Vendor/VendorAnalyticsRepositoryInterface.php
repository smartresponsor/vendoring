<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorAnalytics;

interface VendorAnalyticsRepositoryInterface
{
    public function find(int $id): ?VendorAnalytics;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorAnalytics;

    /**
     * @return list<VendorAnalytics>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
