<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\VendorTransaction;
use App\ValueObject\VendorTransactionData;

interface VendorTransactionManagerInterface
{
    public function createTransaction(VendorTransactionData $data): VendorTransaction;

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction;
}
