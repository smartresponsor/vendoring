<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ops;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;

interface VendorTransactionOperatorPageBuilderServiceInterface
{
    /**
     * @param list<VendorTransactionEntity> $transactions
     */
    public function renderIndex(string $vendorId, array $transactions, ?string $flashMessage = null, ?string $errorMessage = null): string;
}
