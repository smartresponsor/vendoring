<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\VendorTransaction;

/**
 * Persistence contract for vendor transaction repository records.
 */
interface VendorTransactionRepositoryInterface
{
    /**
     * @return list<VendorTransaction>
     */
    public function findByVendorId(string $vendorId): array;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction;

    /**
     * Executes the exists for vendor order project operation for this runtime surface.
     */
    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool;
}
