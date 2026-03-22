<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ValueObject;

final class VendorTransactionData
{
    public function __construct(
        public readonly string $vendorId,
        public readonly string $orderId,
        public readonly ?string $projectId,
        public readonly string $amount,
    ) {
    }
}
