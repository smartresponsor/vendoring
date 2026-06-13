<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Payout;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;

interface VendorPayoutAccountServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function upsertFromPayload(array $payload): VendorPayoutAccountEntity;
}
