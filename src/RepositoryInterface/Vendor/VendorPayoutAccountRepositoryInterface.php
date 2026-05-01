<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use Doctrine\DBAL\Exception;

interface VendorPayoutAccountRepositoryInterface
{
    /** @throws Exception */
    public function get(string $tenantId, string $vendorId): ?VendorPayoutAccountEntity;

    /** @throws Exception */
    public function upsert(VendorPayoutAccountEntity $account): void;
}
