<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorBillingRepository::class)]
#[ORM\Table(name: 'vendor_billing')]
class VendorBillingEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'billing', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] private ?string $iban = null;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] private ?string $swift = null;
    #[ORM\Column(type: 'string', length: 32)] private string $payoutMethod = 'bank';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $billingEmail = null;
    #[ORM\Column(type: 'string', length: 32)] private string $payoutStatus = 'idle';
    public function __construct(VendorEntity $vendor)
    {
        parent::__construct();
        $this->vendor = $vendor;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function update(?string $iban, ?string $swift, string $payoutMethod, ?string $billingEmail): self
    {
        $this->iban = $iban;
        $this->swift = $swift;
        $this->payoutMethod = $payoutMethod;
        $this->billingEmail = $billingEmail;
        $this->touchModified();

        return $this;
    }

    public function markPayoutRequested(): self
    {
        $this->payoutStatus = 'requested';

        return $this;
    }

    public function markPayoutCompleted(): self
    {
        $this->payoutStatus = 'completed';

        return $this;
    }

    public function getPayoutStatus(): string
    {
        return $this->payoutStatus;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getSwift(): ?string
    {
        return $this->swift;
    }

    public function getPayoutMethod(): string
    {
        return $this->payoutMethod;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }
}
