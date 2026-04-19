<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorTransaction;

interface VendorTransactionRepositoryInterface
{
    /**
     * @return list<VendorTransaction>
     */
    public function findByVendorId(string $vendorId): array;

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction;

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool;
}
