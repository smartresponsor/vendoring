<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorShipmentRepository::class)]
#[ORM\Table(name: 'vendor_shipment')]
class VendorShipmentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'shipments')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $externalShipmentId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $carrierCode = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $methodCode = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $trackingNumber = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $shippedAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $deliveredAt = null;
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        parent::__construct('pending');
        $this->vendor = $vendor;
        $this->meta = $meta;
    }

    public function update(?string $externalShipmentId, ?string $carrierCode, ?string $methodCode, ?string $trackingNumber, string $status, array $meta): self
    {
        $this->externalShipmentId = $externalShipmentId;
        $this->carrierCode = $carrierCode;
        $this->methodCode = $methodCode;
        $this->trackingNumber = $trackingNumber;
        $this->status = $status;
        $this->meta = $meta;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getExternalShipmentId()
    {
        return $this->externalShipmentId;
    }

    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    public function getMethodCode()
    {
        return $this->methodCode;
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getShippedAt()
    {
        return $this->shippedAt;
    }

    public function getDeliveredAt()
    {
        return $this->deliveredAt;
    }
}
