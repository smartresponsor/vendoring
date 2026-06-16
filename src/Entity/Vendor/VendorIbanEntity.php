<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorIbanRepository::class)]
#[ORM\Table(name: 'vendor_iban')]
class VendorIbanEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'iban', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64)] private string $iban;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] private ?string $swift = null;
    public function __construct(VendorEntity $vendor, string $iban, ?string $swift = null)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->update($iban, $swift);
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function update(string $iban, ?string $swift): self
    {
        $this->iban = $iban;
        $this->swift = $swift;
        $this->touchModified();

        return $this;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function getSwift(): ?string
    {
        return $this->swift;
    }
}
