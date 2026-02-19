<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\Entity\Vendor\VendorTransaction;
use App\ValueObject\Vendor\VendorTransactionData;

interface VendorTransactionManagerInterface
{
    public function createTransaction(VendorTransactionData $data): VendorTransaction;

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction;
}
