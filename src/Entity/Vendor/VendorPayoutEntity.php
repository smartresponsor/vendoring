<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payouts')]
final class VendorPayoutEntity
{
    /** @param array<string, mixed> $meta */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 36)]
        public string $id,
        #[ORM\Column(name: 'vendor_id', type: 'string', length: 255)]
        public string $vendorId,
        #[ORM\Column(type: 'string', length: 8)]
        public string $currency,
        #[ORM\Column(name: 'gross_cents', type: 'integer')]
        public int $grossCents,
        #[ORM\Column(name: 'fee_cents', type: 'integer')]
        public int $feeCents,
        #[ORM\Column(name: 'net_cents', type: 'integer')]
        public int $netCents,
        #[ORM\Column(type: 'string', length: 32)]
        public string $status,
        #[ORM\Column(name: 'created_at', type: 'string', length: 19)]
        public string $createdAt,
        #[ORM\Column(name: 'processed_at', type: 'string', length: 19, nullable: true)]
        public ?string $processedAt = null,
        #[ORM\Column(type: 'json')]
        public array $meta = [],
    ) {}
}
