<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_payment')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_payment_vendor_provider_method', columns: ['vendor_id', 'provider_code', 'method_code'])]
#[ORM\Index(name: 'idx_vendor_payment_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(name: 'provider_code', type: 'string', length: 64)]
    private string $providerCode;

    #[ORM\Column(name: 'method_code', type: 'string', length: 64)]
    private string $methodCode;

    #[ORM\Column(name: 'external_payment_id', type: 'string', length: 128, nullable: true)]
    private ?string $externalPaymentId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    #[ORM\Column(name: 'is_default', type: 'boolean')]
    private bool $isDefault = false;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @param array<string, mixed> $meta */
    public function __construct(Vendor $vendor, string $providerCode, string $methodCode, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->providerCode = $providerCode;
        $this->methodCode = $methodCode;
        $this->meta = $meta;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    /** @param array<string, mixed> $meta */
    public function update(?string $externalPaymentId = null, ?string $label = null, ?string $status = null, ?bool $isDefault = null, array $meta = []): void
    {
        $this->externalPaymentId = $externalPaymentId;
        $this->label = $label;
        $this->status = null === $status ? $this->status : $status;
        $this->isDefault = null === $isDefault ? $this->isDefault : $isDefault;
        $this->meta = [] === $meta ? $this->meta : $meta;
        $this->updatedAt = new DateTimeImmutable();
    }
}
