<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Entity\Payout;

interface PayoutBatchInterface
{

    public function __construct(public string $id, public string $tenantId, public string $vendorId, public string $periodStart, // Y-m-d public string $periodEnd, // Y-m-d public string $status, // draft|ready|paid|failed public float $amountGross, public float $fees, public float $amountNet, public string $currency, public ?string $providerRef, public string $createdAt);
}
