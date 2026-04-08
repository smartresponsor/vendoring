<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\VendorTransaction;
use App\ValueObject\VendorTransactionData;

/**
 * Application contract for vendor transaction manager operations.
 */
interface VendorTransactionManagerInterface
{
    /**
     * Creates the requested resource from the supplied input.
     */
    public function createTransaction(VendorTransactionData $data): VendorTransaction;

    /**
     * Updates the requested resource state.
     */
    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction;
}
