<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Payout;

/**
 * Application contract for vendor payout provider service operations.
 */
interface VendorPayoutProviderServiceInterface
{
    /** @return array<string, mixed> */
    public function transfer(string $tenantId, string $vendorId, string $provider, string $accountRef, float $amount, string $currency): array;
}
