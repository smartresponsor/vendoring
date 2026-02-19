<?php
declare(strict_types = 1);

namespace App\Entity\Vendor\Payout;
final class PayoutItem
{
    public function __construct(
        public string $id,
        public string $batchId,
        public string $referenceType,  // order|refund|adjustment
        public string $referenceId,
        public float  $amount,
        public string $currency
    )
    {
    }
}
