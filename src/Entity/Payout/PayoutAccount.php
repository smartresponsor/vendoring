<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Payout;

final class PayoutAccount
{
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $vendorId,
        public string $provider,   // stripe_connect|paypal|bank
        public string $accountRef, // acct_xxx / email / IBAN
        public string $currency,
        public bool $active,
        public string $createdAt,
    ) {}
}
