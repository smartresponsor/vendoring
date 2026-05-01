<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ledger;

final class VendorRefundTransactionDTO
{
    public function __construct(public string $tenantId, public string $vendorId, public string $rmaId, public string $orderId, public float $amount, public string $currency) {}
}
