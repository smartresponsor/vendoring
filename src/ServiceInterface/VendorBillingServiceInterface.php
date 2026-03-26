<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorBillingDTO;
use App\Entity\Vendor;
use App\Entity\VendorBilling;

interface VendorBillingServiceInterface
{
    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling;

    public function requestPayout(VendorBilling $billing, int $amountMinor): void;

    public function completePayout(VendorBilling $billing, int $amountMinor): void;
}
