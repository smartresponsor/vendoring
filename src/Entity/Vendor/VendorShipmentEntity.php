<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_shipment')]
#[ORM\Index(name: 'idx_vendor_shipment_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorShipmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'external_shipment_id', type: 'string', length: 128, nullable: true)]
    private ?string $externalShipmentId = null;

    #[ORM\Column(name: 'carrier_code', type: 'string', length: 64, nullable: true)]
    private ?string $carrierCode = null;

    #[ORM\Column(name: 'method_code', type: 'string', length: 64, nullable: true)]
    private ?string $methodCode = null;

    #[ORM\Column(name: 'tracking_number', type: 'string', length: 128, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'pending';

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'shipped_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $shippedAt = null;

    #[ORM\Column(name: 'delivered_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deliveredAt = null;

    /** @param array<string, mixed> $meta */
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->meta = $meta;
    }

    /** @param array<string, mixed> $meta */
    public function update(?string $externalShipmentId = null, ?string $carrierCode = null, ?string $methodCode = null, ?string $trackingNumber = null, ?string $status = null, array $meta = []): void
    {
        $this->externalShipmentId = $externalShipmentId;
        $this->carrierCode = $carrierCode;
        $this->methodCode = $methodCode;
        $this->trackingNumber = $trackingNumber;
        $this->status = null === $status ? $this->status : $status;
        $this->meta = [] === $meta ? $this->meta : $meta;

        if ('shipped' === $this->status && null === $this->shippedAt) {
            $this->shippedAt = new DateTimeImmutable();
        }

        if ('delivered' === $this->status) {
            if (null === $this->shippedAt) {
                $this->shippedAt = new DateTimeImmutable();
            }

            $this->deliveredAt = new DateTimeImmutable();
        } elseif ('pending' === $this->status) {
            $this->deliveredAt = null;
        }
    }
}
