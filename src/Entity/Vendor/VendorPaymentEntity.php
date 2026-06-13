<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorPaymentRepository::class)]
#[ORM\Table(name: 'vendor_payment')]
class VendorPaymentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'payments')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $providerCode = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $methodCode = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $externalPaymentId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $label = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'boolean')] private bool $isDefault = false;
    #[ORM\Column(type: 'json')] private array $meta = [];
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->meta = $meta;
    }

    public function update(string $providerCode, string $methodCode, ?string $externalPaymentId, ?string $label, string $status, bool $isDefault, array $meta): self
    {
        $this->providerCode = $providerCode;
        $this->methodCode = $methodCode;
        $this->externalPaymentId = $externalPaymentId;
        $this->label = $label;
        $this->status = $status;
        $this->isDefault = $isDefault;
        $this->meta = $meta;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getProviderCode()
    {
        return $this->providerCode;
    }

    public function getMethodCode()
    {
        return $this->methodCode;
    }

    public function getExternalPaymentId()
    {
        return $this->externalPaymentId;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isIsDefault()
    {
        return $this->isDefault;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
