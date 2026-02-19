<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Payout;

interface PayoutAccountDTOInterface
{

    public function __construct(public string $tenantId, public string $vendorId, public string $provider, public string $accountRef, public string $currency, public bool $active);
}
