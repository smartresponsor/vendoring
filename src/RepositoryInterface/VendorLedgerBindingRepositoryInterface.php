<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorLedgerBinding;

interface VendorLedgerBindingRepositoryInterface
{
    public function find(int $id): ?VendorLedgerBinding;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorLedgerBinding;

    /**
     * @return list<VendorLedgerBinding>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
