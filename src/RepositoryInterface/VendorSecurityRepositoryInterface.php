<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\EntityInterface\VendorSecurityInterface;

interface VendorSecurityRepositoryInterface
{
    public function find(int $id): ?VendorSecurityInterface;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorSecurityInterface;

    /**
     * @return list<VendorSecurityInterface>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface;
}
