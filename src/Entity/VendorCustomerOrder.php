<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_customer_order')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_customer_order_vendor_external', columns: ['vendor_id', 'external_order_id'])]
#[ORM\Index(name: 'idx_vendor_customer_order_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorCustomerOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(name: 'external_order_id', type: 'string', length: 128)]
    private string $externalOrderId;

    #[ORM\Column(name: 'order_number', type: 'string', length: 64, nullable: true)]
    private ?string $orderNumber = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(type: 'string', length: 8)]
    private string $currency;

    #[ORM\Column(name: 'gross_cents', type: 'integer')]
    private int $grossCents;

    #[ORM\Column(name: 'net_cents', type: 'integer')]
    private int $netCents;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'placed_at', type: 'datetime_immutable')]
    private DateTimeImmutable $placedAt;

    /** @param array<string, mixed> $meta */
    public function __construct(Vendor $vendor, string $externalOrderId, string $status, string $currency, int $grossCents, int $netCents, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->externalOrderId = $externalOrderId;
        $this->status = $status;
        $this->currency = $currency;
        $this->grossCents = $grossCents;
        $this->netCents = $netCents;
        $this->meta = $meta;
        $this->placedAt = new DateTimeImmutable();
    }

    /** @param array<string, mixed> $meta */
    public function update(?string $orderNumber = null, ?string $status = null, ?string $currency = null, ?int $grossCents = null, ?int $netCents = null, array $meta = []): void
    {
        $this->orderNumber = $orderNumber;
        $this->status = null === $status ? $this->status : $status;
        $this->currency = null === $currency ? $this->currency : $currency;
        $this->grossCents = null === $grossCents ? $this->grossCents : $grossCents;
        $this->netCents = null === $netCents ? $this->netCents : $netCents;
        $this->meta = [] === $meta ? $this->meta : $meta;
    }
}
