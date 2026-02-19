<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Ledger;

interface RefundTransactionDTOInterface
{

    public function __construct(public string $tenantId, public string $vendorId, public string $rmaId, public string $orderId, public float $amount, public string $currency);
}
