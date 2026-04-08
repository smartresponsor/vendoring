<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Payout;

use App\Entity\Payout\PayoutAccount;

/**
 * Persistence contract for payout account repository records.
 */
interface PayoutAccountRepositoryInterface
{
    /**
     * Returns the requested runtime state.
     */
    public function get(string $tenantId, string $vendorId): ?PayoutAccount;

    /**
     * Creates or updates the requested aggregate state.
     */
    public function upsert(PayoutAccount $account): void;
}
