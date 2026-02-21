<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor\Payout;

use App\Entity\Vendor\Payout\PayoutAccount;

interface PayoutAccountRepositoryInterface
{
    public function get(string $tenantId, string $vendorId): ?PayoutAccount;

    public function upsert(PayoutAccount $account): void;
}
