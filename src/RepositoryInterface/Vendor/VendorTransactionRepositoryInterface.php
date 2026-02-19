<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorTransaction;

interface VendorTransactionRepositoryInterface
{
    /**
     * @return list<VendorTransaction>
     */
    public function findByVendorId(string $vendorId): array;
}
