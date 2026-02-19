<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Entity\Payout;

interface PayoutAccountInterface
{

    public function __construct(public string $id, public string $tenantId, public string $vendorId, public string $provider, // stripe_connect|paypal|bank public string $accountRef, // acct_xxx / email / IBAN public string $currency, public bool $active, public string $createdAt);
}
