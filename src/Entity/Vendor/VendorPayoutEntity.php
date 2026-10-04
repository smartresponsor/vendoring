<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorPayoutRepository::class)]
#[ORM\Table(
    name: 'vendor_payout',
    indexes: [
        new ORM\Index(name: 'idx_vendor_payout_vendor_id', columns: ['vendor_id']),
        new ORM\Index(name: 'idx_vendor_payout_status', columns: ['payout_status']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_vendor_payout_business_id', columns: ['payout_id']),
    ],
)]
class VendorPayoutEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $payoutId;
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    #[ORM\Column(type: 'integer')] public int $grossCents;
    #[ORM\Column(type: 'integer')] public int $feeCents;
    #[ORM\Column(type: 'integer')] public int $netCents;
    #[ORM\Column(name: 'payout_status', type: 'string', length: 32)] public string $status;
    #[ORM\Column(type: 'string', length: 32, nullable: true)] public ?string $processedAt = null;
    /** @var array<array-key, mixed> */
    #[ORM\Column(type: 'json')] public array $meta = [];
    public function __construct(
        string $id,
        string $vendorId,
        string $currency,
        int $grossCents,
        int $feeCents,
        int $netCents,
        string $status = 'pending',
        mixed $createdAt = null,
        mixed $processedAt = null,
        mixed $meta = [],
    ) {
        parent::__construct($status);
        $this->payoutId = $id;
        $this->vendorId = $vendorId;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->feeCents = $feeCents;
        $this->netCents = $netCents;
        unset($createdAt);
        $this->status = $status;
        $this->processedAt = is_scalar($processedAt) ? (string) $processedAt : null;
        $this->meta = is_array($meta) ? $meta : [];
    }

    public function getPayoutId(): string
    {
        return $this->payoutId;
    }

    public function markProcessed(): self
    {
        $this->status = 'processed';
        $this->processedAt = date('Y-m-d H:i:s');

        return $this;
    }
}
