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
    #[ORM\Column(type: 'string', length: 32, nullable: true)] public ?string $processedAt = null;
    #[ORM\Column(type: 'json')] public array $meta = [];
    public string $createdAt;

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
        $this->status = $status;
        $this->createdAt = is_scalar($createdAt) ? (string) $createdAt : date('Y-m-d H:i:s');
        $this->processedAt = is_scalar($processedAt) ? (string) $processedAt : null;
        $this->meta = is_array($meta) ? $meta : [];
    }

    public function __get(string $name): mixed
    {
        return 'id' === $name ? $this->payoutId : null;
    }

    public function markProcessed(): self
    {
        $this->status = 'processed';
        $this->processedAt = date('Y-m-d H:i:s');

        return $this;
    }
}
