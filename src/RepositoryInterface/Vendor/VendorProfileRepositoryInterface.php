<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorProfile;

interface VendorProfileRepositoryInterface
{
    public function find(int $id): ?VendorProfile;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorProfile;

    /**
     * @return list<VendorProfile>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
