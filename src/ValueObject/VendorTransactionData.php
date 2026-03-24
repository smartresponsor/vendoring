<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ValueObject;

/**
 * Input DTO for vendor transaction creation.
 *
 * This object is consumed by both JSON API and operator surface flows so a single
 * DocBlock can feed OpenAPI/phpDocumentor generation and human-readable developer docs.
 */
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
