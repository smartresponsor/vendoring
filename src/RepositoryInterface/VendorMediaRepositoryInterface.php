<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorMedia;

interface VendorMediaRepositoryInterface
{
    public function find(int $id): ?VendorMedia;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorMedia;

    /**
     * @return list<VendorMedia>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
