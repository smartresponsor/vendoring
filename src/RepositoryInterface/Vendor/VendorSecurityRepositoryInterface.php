<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorSecurity;

interface VendorSecurityRepositoryInterface
{
    public function find(int $id): ?VendorSecurity;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorSecurity;

    /**
     * @return list<VendorSecurity>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
