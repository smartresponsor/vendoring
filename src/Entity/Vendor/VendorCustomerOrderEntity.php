<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCustomerOrderRepository::class)]
#[ORM\Table(name: 'vendor_customer_order')]
class VendorCustomerOrderEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'customerOrders')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $externalOrderId = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $orderNumber = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $currency = '';
    #[ORM\Column(type: 'integer')] private int $grossCents = 0;
    #[ORM\Column(type: 'integer')] private int $netCents = 0;
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $placedAt = null;
    public function __construct(VendorEntity $vendor, string $externalOrderId, string $currency, int $grossCents, int $netCents, array $meta = [])
    {
        parent::__construct('placed');
        $this->vendor = $vendor;
        $this->externalOrderId = $externalOrderId;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->netCents = $netCents;
        $this->meta = $meta;
        $this->placedAt = new \DateTimeImmutable();
    }

    public function update(?string $orderNumber, string $status, string $currency, int $grossCents, int $netCents, array $meta): self
    {
        $this->orderNumber = $orderNumber;
        $this->status = $status;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->netCents = $netCents;
        $this->meta = $meta;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getExternalOrderId()
    {
        return $this->externalOrderId;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getGrossCents()
    {
        return $this->grossCents;
    }

    public function getNetCents()
    {
        return $this->netCents;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getPlacedAt()
    {
        return $this->placedAt;
    }
}
