<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorBillingDTO;
use App\Entity\Vendor;
use App\Entity\VendorBilling;

/**
 * Application contract for vendor billing service operations.
 */
interface VendorBillingServiceInterface
{
    /**
     * Creates or updates the requested aggregate state.
     */
    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling;

    /**
     * Executes the request payout operation for this runtime surface.
     */
    public function requestPayout(VendorBilling $billing, int $amountMinor): void;

    /**
     * Executes the complete payout operation for this runtime surface.
     */
    public function completePayout(VendorBilling $billing, int $amountMinor): void;
}
