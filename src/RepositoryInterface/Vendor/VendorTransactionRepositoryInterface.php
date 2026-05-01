<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;

interface VendorTransactionRepositoryInterface
{
    /**
     * @return list<VendorTransactionEntity>
     */
    public function findByVendorId(string $vendorId): array;

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransactionEntity;

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool;
}
