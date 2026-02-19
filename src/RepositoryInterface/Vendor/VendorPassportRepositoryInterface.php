<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorPassport;

interface VendorPassportRepositoryInterface
{
    public function find(int $id): ?VendorPassport;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorPassport;

    /**
     * @return list<VendorPassport>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
