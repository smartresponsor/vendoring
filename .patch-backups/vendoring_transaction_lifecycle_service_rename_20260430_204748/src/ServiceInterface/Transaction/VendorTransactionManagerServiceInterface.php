<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;

interface VendorTransactionManagerServiceInterface
{
    /**
     * @throws \Throwable
     */
    public function createTransaction(VendorTransactionDataValueObject $data): VendorTransactionEntity;

    public function updateStatus(VendorTransactionEntity $tx, string $status): VendorTransactionEntity;
}
