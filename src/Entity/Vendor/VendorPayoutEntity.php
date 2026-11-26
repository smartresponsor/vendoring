<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorPayoutRepository::class)]
#[ORM\Table(name: 'vendor_payout')]
#[ORM\Index(name: 'idx_vendor_payout_vendor_id', columns: ['vendor_id'])]
#[ORM\Index(name: 'idx_vendor_payout_tenant_vendor', columns: ['tenant_id', 'vendor_id'])]
#[ORM\Index(name: 'idx_vendor_payout_status', columns: ['payout_status'])]
#[ORM\UniqueConstraint(name: 'uniq_vendor_payout_business_id', columns: ['payout_id'])]
#[ORM\UniqueConstraint(name: 'uniq_vendor_payout_pending_scope', columns: ['tenant_id', 'vendor_id', 'currency', 'pending_slot'])]
class VendorPayoutEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $payoutId;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] public ?string $tenantId = null;
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    #[ORM\Column(type: 'integer')] public int $grossCents;
    #[ORM\Column(type: 'integer')] public int $feeCents;
    #[ORM\Column(type: 'integer')] public int $netCents;
    #[ORM\Column(name: 'payout_status', type: 'string', length: 32)] public string $status;
    #[ORM\Column(type: 'integer', nullable: true)] public ?int $pendingSlot = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)] public ?string $processedAt = null;
    /** @var array<array-key, mixed> */
    #[ORM\Column(type: 'json')] public array $meta = [];
    public function __construct(
        string $id,
        string $tenantId,
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
        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
        parent::__construct($status);
        $this->tenantId = $tenantId;
        $this->payoutId = $id;
        $this->vendorId = $vendorId;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->feeCents = $feeCents;
        $this->netCents = $netCents;
        unset($createdAt);
        $this->status = $status;
        $this->pendingSlot = 'pending' === $status ? 1 : null;
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
        $this->pendingSlot = null;
        $this->processedAt = date('Y-m-d H:i:s');

        return $this;
    }
}
