<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Payout;

use App\Vendoring\Entity\Payout\PayoutAccount;
use Doctrine\DBAL\Exception;

interface PayoutAccountRepositoryInterface
{
    /** @throws Exception */
    public function get(string $tenantId, string $vendorId): ?PayoutAccount;

    /** @throws Exception */
    public function upsert(PayoutAccount $account): void;
}
