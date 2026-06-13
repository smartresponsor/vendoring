<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorPassportRepository::class)]
#[ORM\Table(name: 'vendor_passport')]
class VendorPassportEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'passport', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64)] private string $taxId;
    #[ORM\Column(type: 'string', length: 8)] private string $country;
    #[ORM\Column(type: 'boolean')] private bool $verified = false;
    public function __construct(VendorEntity $vendor, string $taxId, string $country)
    {
        parent::__construct('pending');
        $this->vendor = $vendor;
        $this->taxId = $taxId;
        $this->country = $country;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getTaxId(): string
    {
        return $this->taxId;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function markVerified(): self
    {
        $this->verified = true;

        return $this->setStatus('verified');
    }
}
