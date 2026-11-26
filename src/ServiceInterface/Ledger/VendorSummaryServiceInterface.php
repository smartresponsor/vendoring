<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Ledger;

interface VendorSummaryServiceInterface
{
    /**
     * @return array{vendorId: string, from: string, to: string, currency: string, balances: array<string, float|int>}
     */
    public function build(string $tenantId, string $vendorId, string $from, string $to, string $currency): array;
}
