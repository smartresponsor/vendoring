<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

use App\Entity\Vendor\VendorTransaction;

interface VendorTransactionOperatorPageBuilderInterface
{
    /**
     * @param list<VendorTransaction> $transactions
     */
    public function renderIndex(string $vendorId, array $transactions, ?string $flashMessage = null, ?string $errorMessage = null): string;
}
