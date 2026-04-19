<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Payout;

use App\Vendoring\Entity\Payout\PayoutAccount;

interface VendorPayoutAccountServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function upsertFromPayload(array $payload): PayoutAccount;
}
