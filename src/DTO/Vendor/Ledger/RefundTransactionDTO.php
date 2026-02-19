<?php
declare(strict_types = 1);

namespace App\DTO\Vendor\Ledger;
final class RefundTransactionDTO
{
    public function __construct(public string $tenantId, public string $vendorId, public string $rmaId, public string $orderId, public float $amount, public string $currency)
    {
    }
}
