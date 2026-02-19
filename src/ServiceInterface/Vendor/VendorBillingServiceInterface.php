<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\DTO\Vendor\VendorBillingDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorBilling;

interface VendorBillingServiceInterface
{
    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling;

    public function requestPayout(VendorBilling $billing, int $amountMinor): void;

    public function completePayout(VendorBilling $billing, int $amountMinor): void;
}
