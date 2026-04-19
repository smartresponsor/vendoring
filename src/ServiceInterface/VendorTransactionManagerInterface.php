<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Entity\VendorTransaction;
use App\Vendoring\ValueObject\VendorTransactionData;

interface VendorTransactionManagerInterface
{
    /**
     * @throws \Throwable
     */
    public function createTransaction(VendorTransactionData $data): VendorTransaction;

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction;
}
