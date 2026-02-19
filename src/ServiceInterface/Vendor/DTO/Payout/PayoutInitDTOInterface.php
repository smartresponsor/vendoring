<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Payout;

interface PayoutInitDTOInterface
{

    public function __construct(public string $tenantId, public string $vendorId, public string $periodStart, public string $periodEnd, public string $currency);
}
