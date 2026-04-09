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
final readonly class VendorTransactionData
{
    public function __construct(
        public string $vendorId,
        public string $orderId,
        public ?string $projectId,
        public string $amount,
    ) {
    }
}
