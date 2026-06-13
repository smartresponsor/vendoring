<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorPayoutRepository::class)]
#[ORM\Table(name: 'vendor_payout')]
class VendorPayoutEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $payoutId;
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    #[ORM\Column(type: 'integer')] public int $grossCents;
    #[ORM\Column(type: 'integer')] public int $feeCents;
    #[ORM\Column(type: 'integer')] public int $netCents;
    #[ORM\Column(type: 'string', length: 32)] public string $status;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] public ?\DateTimeImmutable $processedAt = null;
    #[ORM\Column(type: 'json')] public array $meta = [];
    public function __construct(string $payoutId, string $vendorId, string $currency, int $grossCents, int $feeCents, int $netCents, string $status = 'pending', array $meta = [])
    {
        parent::__construct($status);
        $this->payoutId = $payoutId;
        $this->vendorId = $vendorId;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->feeCents = $feeCents;
        $this->netCents = $netCents;
        $this->status = $status;
        $this->meta = $meta;
    }

    public function markProcessed(): self
    {
        $this->status = 'processed';
        $this->processedAt = new \DateTimeImmutable();

        return $this;
    }
}
