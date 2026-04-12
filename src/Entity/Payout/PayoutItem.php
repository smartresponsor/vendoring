<?php

declare(strict_types=1);

namespace App\Entity\Payout;

final class PayoutItem
{
    public function __construct(
        public string $id,
        public string $payoutId,
        public string $entryId,
        public int $amountCents,
    ) {}
}
