<?php
declare(strict_types = 1);

namespace App\Entity\Vendor\Payout;

final class Payout
{
    public function __construct(
        public string  $id,
        public string  $vendorId,
        public string  $currency,
        public int     $grossCents,
        public int     $feeCents,
        public int     $netCents,
        public string  $status, // pending|processed|failed
        public string  $createdAt,
        public ?string $processedAt = null,
        public array   $meta = []
    )
    {
    }
}
