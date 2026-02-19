<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorBilling;

interface VendorBillingRepositoryInterface
{
    public function find(int $id): ?VendorBilling;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorBilling;

    /**
     * @return list<VendorBilling>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
